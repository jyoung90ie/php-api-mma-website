<?php

namespace models;

use InvalidArgumentException;

class Fight
{
    // validation constants
    const ROUNDS_MIN = 3;
    const ROUNDS_MAX = 5;
    const PERMISSION_AREA = 'FIGHTS';

    private $fightId = null;
    private $eventId = null;
    private $refereeId = null;
    private $titleBout = null;
    private $weightClassId = null;
    private $numOfRounds = null;
    private $results = null;

    private $athleteId1;
    private $athleteId2;
    private $fightAthleteId1;
    private $fightAthleteId2;


    private $db;

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


        $query = $this->db->query($query);
        $result = $query->fetchAll();
        $this->results = $result;

        return $result;
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

    /**
     * @param array|null $data
     * @return array|false
     */
    public function create(array $data = null)
    {
        if (!is_null($data)) {
            $this->processData($data);
        }

        $this->validateData();

        // start sql transaction
        $this->db->beginTransaction();

        $query = "INSERT INTO Fights
                        (EventID, RefereeID, TitleBout, WeightClassID, NumOfRounds)
                    VALUES 
                        (?, ?, ?, ?, ?)";

        $query = $this->db->prepare($query);

        $query->execute([
            $this->eventId,
            $this->refereeId,
            $this->titleBout,
            $this->weightClassId,
            $this->numOfRounds
        ]);

        if ($query->rowCount() > 0) {

            $result['FightID'] = $this->db->lastInsertId();

            if (isset($this->athleteId1) && isset($this->athleteId2)) {
                // create fight athletes records
                $fightAthleteQ = "INSERT INTO FightAthletes 
                                    (FightID, AthleteID)
                                VALUES
                                    (?, ?),
                                    (?, ?)";

                $fightAthleteQ = $this->db->prepare($fightAthleteQ);
                $fightAthleteQ->execute([
                    $result['FightID'], $this->athleteId1,
                    $result['FightID'], $this->athleteId2
                ]);

                if ($fightAthleteQ->rowCount() == 2) {
                    // everything went as expected, return result
                    $this->db->commit();
                    return $fightAthleteQ->rowCount();
                }

                // something went wrong - revert
                $this->db->rollBack();
            }
        }
        return false;
    }

    public function update(int $id, array $data = null): int
    {
        $this->setFightId($id);

        if (!is_null($data)) {
            $this->processData($data);
        }

        $this->validateData();

        if (isset($data['FightAthleteID1']) && is_numeric($data['FightAthleteID1'])) {
            $this->fightAthleteId1 = intval($data['FightAthleteID1']);
        } else {
            throw new InvalidArgumentException('FightAthleteID1 is invalid');
        }

        if (isset($data['FightAthleteID2']) && is_numeric($data['FightAthleteID2'])) {
            $this->fightAthleteId2 = intval($data['FightAthleteID2']);
        } else {
            throw new InvalidArgumentException('FightAthleteID2 is invalid');
        }


        $query = "UPDATE Fights
                    SET 
                        EventID=:eventId,
                        RefereeID=:refereeId,
                        TitleBout=:titleBout,
                        WeightClassID=:weightClassId,
                        NumOfRounds=:numOfRounds
                    WHERE 
                        FightID=:fightId";


        $query = $this->db->prepare($query);

        $params = [
            ':eventId' => $this->eventId,
            ':refereeId' => $this->refereeId,
            ':titleBout' => $this->titleBout,
            ':weightClassId' => $this->weightClassId,
            ':numOfRounds' => $this->numOfRounds,
            ':fightId' => $this->fightId
        ];

        $queryCounter = 0;

        $query->execute($params);

        if ($query->rowCount() > 0) {
            $queryCounter++;
        }

        // update fight athletes
        $updateQuery = "UPDATE FightAthletes SET AthleteID=:athleteId WHERE FightAthleteID=:fightAthleteId";
        $updateQuery = $this->db->prepare($updateQuery);

        $updateQueryData = [
            [':athleteId' => $this->athleteId1, ':fightAthleteId' => $this->fightAthleteId1],
            [':athleteId' => $this->athleteId2, ':fightAthleteId' => $this->fightAthleteId2]
        ];

        foreach ($updateQueryData as $data) {
            $updateQuery->execute($data);

            if ($updateQuery->rowCount() > 0) {
                $queryCounter++;
            }
        }

        return $queryCounter;
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

        $this->db->rollBack();
        return false;
    }

// utility functions
    private function processData(array $data): void
    {
        $this->setEventId($data['EventID']);
        $this->setRefereeId($data['RefereeID']);
        $this->setTitleBout($data['TitleBout']);
        $this->setWeightClassId($data['WeightClassID']);
        $this->setNumOfRounds($data['NumOfRounds']);

        if (isset($data['AthleteID1']) && is_numeric($data['AthleteID1'])) {
            $this->athleteId1 = intval($data['AthleteID1']);
        } else {
            throw new InvalidArgumentException('AthleteID1 is invalid');
        }

        if (isset($data['AthleteID2']) && is_numeric($data['AthleteID2'])) {
            $this->athleteId2 = intval($data['AthleteID2']);
        } else {
            throw new InvalidArgumentException('AthleteID2 is invalid');
        }
    }

    private
    function validateData(): void
    {
        if (is_null($this->eventId) || is_null($this->refereeId) || is_null($this->titleBout) || is_null($this->weightClassId)
            || is_null($this->numOfRounds) || is_null($this->athleteId1) || is_null($this->athleteId2)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    private
    function validateIdSet(): void
    {
        if (!isset($this->fightId)) {
            throw new InvalidArgumentException("Object Id has no value");
        }
    }


// getters and setters

    /**
     * @return int|null
     */
    public
    function getFightId(): ?int
    {
        return $this->fightId;
    }

    /**
     * @param int|null $fightId
     */
    public
    function setFightId(?int $fightId): void
    {
        if ($fightId <= 0) {
            throw new InvalidArgumentException("Invalid ID");
        }
        $this->fightId = intval($fightId);
    }

    /**
     * @return int|null
     */
    public
    function getEventId(): ?int
    {
        return $this->eventId;
    }

    /**
     * @param int|null $eventId
     */
    public
    function setEventId(?int $eventId): void
    {
        if ($eventId <= 0) {
            throw new InvalidArgumentException("Invalid Event ID");
        }

        $this->eventId = intval($eventId);
    }

    /**
     * @return int|null
     */
    public
    function getRefereeId(): ?int
    {
        return $this->refereeId;
    }

    /**
     * @param int|null $refereeId
     */
    public
    function setRefereeId(?int $refereeId): void
    {
        if ($refereeId <= 0) {
            throw new InvalidArgumentException("Invalid Referee ID");
        }

        $this->refereeId = intval($refereeId);
    }

    /**
     * @return int|null
     */
    public
    function getTitleBout(): ?int
    {
        return $this->titleBout;
    }

    /**
     * @param bool|null $titleBout
     */
    public
    function setTitleBout(?bool $titleBout): void
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
    public
    function getWeightClassId(): ?int
    {
        return $this->weightClassId;
    }

    /**
     * @param int|null $weightClassId
     */
    public
    function setWeightClassId(?int $weightClassId): void
    {
        if ($weightClassId <= 0) {
            throw new InvalidArgumentException("Invalid Weight Class ID");
        }

        $this->weightClassId = $weightClassId;
    }

    /**
     * @return int|null
     */
    public
    function getNumOfRounds(): ?int
    {
        return $this->numOfRounds;
    }

    /**
     * @param int|null $numOfRounds
     */
    public
    function setNumOfRounds(?int $numOfRounds): void
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
    public
    function getResults()
    {
        return $this->results;
    }

    /**
     * @return mixed
     */
    public
    function getAthleteId2()
    {
        return $this->athleteId2;
    }

    /**
     * @param mixed $athleteId2
     */
    public
    function setAthleteId2($athleteId2): void
    {
        $this->athleteId2 = $athleteId2;
    }

    /**
     * @return mixed
     */
    public
    function getAthleteId1()
    {
        return $this->athleteId1;
    }

    /**
     * @param mixed $athleteId1
     */
    public
    function setAthleteId1($athleteId1): void
    {
        $this->athleteId1 = $athleteId1;
    }


}