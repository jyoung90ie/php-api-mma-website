<?php

namespace models;

use InvalidArgumentException;
use PDO;
use PDOException;

class Fight
{
    // validation constants
    const ROUNDS_MIN = 3;
    const ROUNDS_MAX = 5;
    const PERMISSION_AREA = 'FIGHTS';

    private ?int $fightId = null;
    private ?int $eventId = null;
    private ?int $refereeId = null;
    private ?int $titleBout = null;
    private ?int $weightClassId = null;
    private ?int $numOfRounds = null;
    private $results = null;

    private FightAthlete $fightAthlete1;
    private FightAthlete $fightAthlete2;


    private PDO $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        $this->setFightId($id);

        $query = "SELECT 
                        F.FightID,
                        F.EventID,
                        F.TitleBout,
                        F.NumOfRounds,
                        WC.WeightClassID,
                        WC.WeightClass,
                        R.RefereeID,
                        RT.ResultDescription AS 'Outcome', 
                        WA.AthleteName As 'Winner',
                        FR.WinnerAthleteID,
                        FR.WinRound,
                        FR.WinRoundTime
                    FROM 
                        Fights F
                    LEFT JOIN Events E ON E.EventID = F.EventID
                    LEFT JOIN WeightClasses WC on WC.WeightClassID = F.WeightClassID
                    LEFT JOIN Referees R ON R.RefereeID = F.RefereeID
                    LEFT JOIN FightResults FR ON FR.FightID = F.FightID
                    LEFT JOIN FightAthletes FA ON FA.FightID = F.FightID
                    LEFT JOIN Athletes A on FA.AthleteID = A.AthleteID
                    LEFT JOIN Athletes WA on FA.AthleteID = FR.WinnerAthleteID
                    LEFT JOIN ResultTypes RT on RT.ResultTypeID = FR.ResultTypeID
                    WHERE F.FightID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->fightId]);

            if ($query->rowCount() > 0) {

                $result = $query->fetch();

                $this->fightId = $result['FightID'];
                $this->eventId = $result['EventID'];
                $this->refereeId = $result['RefereeID'];
                $this->titleBout = $result['TitleBout'];
                $this->weightClassId = $result['WeightClassID'];
                $this->numOfRounds = $result['NumOfRounds'];
                $result['Athletes'] = [];

                $this->results = $result;

                // add athlete data
                $query = "SELECT 
                            FightAthleteID,
                            FightID,
                            A.AthleteName,
                            A.AthleteID, 
                            stats_strikesLanded,
                            stats_strikesThrown,
                            stats_significantStrikesLanded,
                            stats_significantStrikesThrown,
                            stats_takedownsLanded,
                            stats_takedownsThrown,
                            stats_positionReversals,
                            stats_knockDowns,
                            stats_submissionAttempts
                        FROM 
                            FightAthletes
                        LEFT JOIN 
                            Athletes A on FightAthletes.AthleteID = A.AthleteID
                        WHERE FightID=?
                        ";
                $query = $this->db->prepare($query);
                $query->execute([$this->fightId]);

                if ($query->rowCount() > 0) {
                    $athleteData = $query->fetchAll();
                    $result['Athletes'] = $athleteData;
                }

                return $result;
            }
            return false;
        } catch (PDOException | \Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function getAll(int $limit, int $start): array
    {
        $query = "SELECT 
                        F.FightID,
                        F.EventID,
                        E.EventDate, 
                        E.EventLocation,
                        F.TitleBout,
                        WC.WeightClassID,
                        WC.WeightClass,
                        F.NumOfRounds,
                        R.RefereeID,
                        R.RefereeName,
                        RT.ResultDescription AS 'Outcome', 
                        WA.AthleteName As 'Winner',
                    FROM 
                        Fights F
                    LEFT JOIN Events E ON E.EventID = F.EventID
                    LEFT JOIN WeightClasses WC on WC.WeightClassID = F.WeightClassID
                    LEFT JOIN Referees R ON R.RefereeID = F.RefereeID
                    LEFT JOIN FightResults FR ON FR.FightID = F.FightID
                    LEFT JOIN FightAthletes FA ON FA.FightID = F.FightID
                    LEFT JOIN Athletes WA on FA.AthleteID = FR.WinnerAthleteID
                    LEFT JOIN ResultTypes RT on RT.ResultTypeID = FR.ResultTypeID
                    LIMIT $start, $limit 
                ";

        try {
            $query = $this->db->query($query);
            $result = $query->fetchAll();
            $this->results = $result;

            return $result;
        } catch (PDOException | \Exception $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * Retrieves the total records in the database - used for pagination, to calculate pages.
     *
     * @return int total number of records.
     */
    public function getTotal(): int
    {
        $query = $this->db->query("SELECT * FROM Fights");
        return $query->rowCount();
    }

    public function create(array $data = null): array
    {
        if (!is_null($data)) {
            $this->processData($data);
        }

        $this->validateData();

        $query = "INSERT INTO Fights
                        (EventID, RefereeID, TitleBout, WeightClassID, NumOfRounds)
                    VALUES 
                        (?, ?, ?, ?, ?)";

        try {
            $query = $this->db->prepare($query);

            $query->execute([
                $this->eventId,
                $this->refereeId,
                $this->titleBout,
                $this->weightClassId,
                $this->numOfRounds
            ]);

            $result['FightID'] = $this->db->lastInsertId();

            $this->fightAthlete1->setFightId($result['FightID']);
            $this->fightAthlete2->setFightId($result['FightID']);

            // create fight athletes
            if ($this->fightAthlete1->create()) {
                $result['FightAthleteID1'] = $this->db->lastInsertId();
            }

            if ($this->fightAthlete2->create()) {
                $result['FightAthleteID2'] = $this->db->lastInsertId();
            }

            return $result;
        } catch (PDOException | \Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function update(int $id, array $data = null): int
    {
        $this->setFightId($id);

        if (!is_null($data)) {
            $this->processData($data);
        }

        $this->validateData();

        $query = "UPDATE Fights
                    SET 
                        EventID = ?,
                        RefereeID = ?,
                        TitleBout = ?,
                        WeightClassID = ?,
                        NumOfRounds = ?
                WHERE 
                        FightID = ?";

        try {
            $query = $this->db->prepare($query);

            $query->execute([
                $this->eventId,
                $this->refereeId,
                $this->titleBout,
                $this->weightClassId,
                $this->numOfRounds,
                $this->fightId
            ]);

            return $query->rowCount();
        } catch (PDOException | \Exception $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * Deletes all records associated with the specified fight_id.
     *
     * This will delete entries from the below tables. The order below is the order the deletes are executed to avoid
     * foreign key constraints.
     *  FightResults
     *  FightAthletes
     *  Fights
     *
     * @param int $id - the FightID to be deleted
     * @return bool - true if successful
     */
    public function delete(int $id): bool
    {
        $this->setFightId($id);
        $this->validateIdSet();

        $this->db->beginTransaction();

        $queries = [
            "DELETE FROM FightResults WHERE FightID = ?;",
            "DELETE FROM FightAthletes WHERE FightID = ?;",
            "DELETE FROM Fights WHERE FightID = ?;"
        ];

        try {
            $executionCounter = 0;
            foreach ($queries as $query) {
                $query = $this->db->prepare($query);
                $query->execute([$this->fightId]);

                if ($query->rowCount() > 0) {
                    $executionCounter++;
                }
            }

            if ($executionCounter > 0) {
                $this->db->commit();
                return true;
            }

            return false;
        } catch (PDOException | \Exception $exception) {
            $this->db->rollBack();
            die($exception->getMessage());
        }
    }

    // utility functions
    private function processData(array $data): void
    {
        try {
            $this->setEventId($data['EventID']);
            $this->setRefereeId($data['RefereeID']);
            $this->setTitleBout($data['TitleBout']);
            $this->setWeightClassId($data['WeightClassID']);
            $this->setNumOfRounds($data['NumOfRounds']);

            if (isset($data['AthleteID1'])) {
                $this->fightAthlete1 = new FightAthlete($this->db);
                $this->fightAthlete1->setAthleteId($data['AthleteID1']);
            }

            if (isset($data['AthleteID2'])) {
                $this->fightAthlete2 = new FightAthlete($this->db);
                $this->fightAthlete2->setAthleteId($data['AthleteID2']);
            }

        } catch (\TypeError | \Exception $exception) {
            exit($exception->getMessage());
        }
    }

    private function validateData(): void
    {
        if (is_null($this->eventId) || is_null($this->refereeId) || is_null($this->titleBout) || is_null($this->weightClassId)
            || is_null($this->numOfRounds)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    private function validateIdSet(): void
    {
        if (!isset($this->fightId)) {
            throw new InvalidArgumentException("Object Id has no value");
        }
    }



    // getters and setters

    /**
     * @return int|null
     */
    public function getFightId(): ?int
    {
        return $this->fightId;
    }

    /**
     * @param int|null $fightId
     */
    public function setFightId(?int $fightId): void
    {
        if ($fightId <= 0) {
            throw new InvalidArgumentException("Invalid ID");
        }
        $this->fightId = $fightId;
    }

    /**
     * @return int|null
     */
    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    /**
     * @param int|null $eventId
     */
    public function setEventId(?int $eventId): void
    {
        if ($eventId <= 0) {
            throw new InvalidArgumentException("Invalid Event ID");
        }

        $this->eventId = $eventId;
    }

    /**
     * @return int|null
     */
    public function getRefereeId(): ?int
    {
        return $this->refereeId;
    }

    /**
     * @param int|null $refereeId
     */
    public function setRefereeId(?int $refereeId): void
    {
        if ($refereeId <= 0) {
            throw new InvalidArgumentException("Invalid Referee ID");
        }

        $this->refereeId = floatval($refereeId);
    }

    /**
     * @return int|null
     */
    public function getTitleBout(): ?int
    {
        return $this->titleBout;
    }

    /**
     * @param bool|null $titleBout
     */
    public function setTitleBout(?bool $titleBout): void
    {
        if ($titleBout) {
            $this->titleBout = 1;
        } else {
            $this->titleBout = 0;
        }
    }

    /**
     * @return int|null
     */
    public function getWeightClassId(): ?int
    {
        return $this->weightClassId;
    }

    /**
     * @param int|null $weightClassId
     */
    public function setWeightClassId(?int $weightClassId): void
    {
        if ($weightClassId <= 0) {
            throw new InvalidArgumentException("Invalid Weight Class ID");
        }

        $this->weightClassId = $weightClassId;
    }

    /**
     * @return int|null
     */
    public function getNumOfRounds(): ?int
    {
        return $this->numOfRounds;
    }

    /**
     * @param int|null $numOfRounds
     */
    public function setNumOfRounds(?int $numOfRounds): void
    {
        if ($numOfRounds < self::ROUNDS_MIN || $numOfRounds > self::ROUNDS_MAX) {
            throw new InvalidArgumentException("Number of rounds must be between " . self::ROUNDS_MIN . "-" .
                self::ROUNDS_MAX);
        }

        $this->numOfRounds = $numOfRounds;
    }

    /**
     * @return null
     */
    public function getResults()
    {
        return $this->results;
    }


}