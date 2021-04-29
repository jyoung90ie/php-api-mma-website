<?php

namespace models;

use InvalidArgumentException;

class Athlete
{
    // validation constants
    const NAME_MIN_LENGTH = 5;
    const NAME_MAX_LENGTH = 100;
    const HEIGHT_MIN = 100;
    const HEIGHT_MAX = 250;
    const REACH_MIN = 100;
    const REACH_MAX = 250;
    const PERMISSION_AREA = 'ATHLETES';

    private $athleteId = null;
    private $name = null;
    private $height = null;
    private $reach = null;
    private $stanceId = null;
    private $dob = null;
    private $results = null;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Returns data on specified athlete, including fights - note fights are paginated.
     *
     * @param int $id the athlete id
     * @param int $limit the number of results to return
     * @param int $start where to start returning records from
     * @return array|false single athlete with list of fights
     */
    public function getOne(int $id, int $limit = 5, int $start = 0)
    {
        $this->setAthleteId($id);

        $query = "SELECT
                    A.*,
                    SUM(FA.stats_strikesThrown) AS TotalStrikesThrown,
                    SUM(FA.stats_strikesLanded) AS TotalStrikesLanded,
                    SUM(FA.stats_significantStrikesThrown) AS TotalSignificantStrikesThrown,
                    SUM(FA.stats_significantStrikesLanded) AS TotalSignificantStrikesLanded,
                    SUM(FA.stats_takedownsThrown) AS TotalTakedownsThrown,
                    SUM(FA.stats_takedownsLanded) AS TotalTakedownsLanded,
                    SUM(FA.stats_submissionAttempts) AS TotalSubmissionAttemps,
                    SUM(FA.stats_knockDowns) AS TotalKnockdowns,
                    SUM(FA.stats_positionReversals) AS TotalPositionReversals,
                    COUNT(F.FightID) AS TotalFights,
                    SUM(IF(FR.WinnerAthleteID=A.AthleteID, 1, 0)) AS TotalWins,
                    SUM(IF(RT.ResultDescription='Draw', 1, 0)) AS TotalDraws,
                    SUM(IF(RT.ResultDescription='Decision%', 1, 0)) AS TotalDecisionWins,
                    SUM(IF(RT.ResultDescription='Submission', 1, 0)) AS TotalSubmissions
                FROM Athletes A
                LEFT JOIN FightAthletes FA on FA.AthleteID = A.AthleteID
                LEFT JOIN Fights F on F.FightID = FA.FightID                
                LEFT JOIN WeightClasses WC on F.WeightClassID = WC.WeightClassID
                LEFT JOIN Referees R on F.RefereeID = R.RefereeID
                LEFT JOIN FightResults FR on F.FightID = FR.FightID
                LEFT JOIN ResultTypes RT on FR.ResultTypeID = RT.ResultTypeID
                WHERE A.AthleteID = ?
                GROUP BY A.AthleteID
                ";

        $query = $this->db->prepare($query);
        $query->execute([$this->athleteId]);

        if ($query->rowCount() > 0) {
            $athlete = $query->fetch();

            $this->athleteId = $athlete['AthleteID'];
            $this->name = $athlete['AthleteName'];
            $this->height = $athlete['AthleteHeightInCM'];
            $this->reach = $athlete['AthleteReachInCM'];
            $this->stanceId = $athlete['AthleteStanceID'];
            $this->dob = $athlete['AthleteDOB'];

            $this->results = $athlete;

            // get fights athlete has competed in
            $query = "SELECT F.*,
                                WC.WeightClass,
                                R.RefereeName,
                                FR.WinnerAthleteID,
                                RT.ResultDescription,
                                FR.WinRound,
                                FR.WinRoundTime
                                
                            FROM Fights F 
                            LEFT JOIN FightAthletes FA on F.FightID = FA.FightID
                            LEFT JOIN Athletes A on A.AthleteID = FA.AthleteID
                            LEFT JOIN WeightClasses WC on F.WeightClassID = WC.WeightClassID
                            LEFT JOIN Referees R on F.RefereeID = R.RefereeID
                            LEFT JOIN FightResults FR on F.FightID = FR.FightID
                            LEFT JOIN ResultTypes RT on FR.ResultTypeID = RT.ResultTypeID
                            WHERE FA.AthleteID = ? 
                            LIMIT $start, $limit
                            ";
            $query = $this->db->prepare($query);
            $query->execute([$this->athleteId]);

            $athleteData = $athlete;
            $athleteData['Fights'] = [];

            if ($query->rowCount() > 0) {
                $fights = $query->fetchAll();
                foreach ($fights as $fight) {
                    $athleteId = $this->db->prepare("SELECT AthleteID FROM FightAthletes WHERE FightID=?;");
                    $athleteId->execute([$fight['FightID']]);

                    if ($athleteId->rowCount() > 0) {
                        $athletes = $athleteId->fetchAll();

                        // create list of athlete id's so 1 query can retrieve them all
                        $athleteIdList = [];
                        foreach ($athletes as $athlete) {
                            array_push($athleteIdList, $athlete['AthleteID']);
                        }

                        $placeholders = str_repeat('?,', sizeof($athleteIdList) - 1) . '?';
                        $athleteQuery = "SELECT * FROM Athletes WHERE AthleteID IN ($placeholders);";
                        $athleteQuery = $this->db->prepare($athleteQuery);

                        $athleteQuery->execute($athleteIdList);

                        if ($athleteQuery->rowCount() > 0) {
                            $athletes = $athleteQuery->fetchAll();
                            $fightAthleteData['Athletes'] = [];
                            // loop through athletes and add winner flag
                            foreach ($athletes as $athlete) {
                                $athlete['Winner'] = ($fight['WinnerAthleteID'] == $athlete['AthleteID'] ? 1 : 0);
                                array_push($fightAthleteData['Athletes'], $athlete);
                            }
                            array_push($athleteData['Fights'], array_merge($fight, $fightAthleteData));
                        }
                    }
                }
            }
            return $athleteData;
        }
        return false;
    }

    /**
     * Return list of athletes sorted by name in ascending order - results are limited to 5 records by default.
     *
     * @param int $limit the number of athletes to return
     * @param int $start first record to return from
     * @return array|false list of athletes
     */
    public function getAll(int $limit = 5, int $start = 0): array
    {
        $query = "SELECT * FROM Athletes ORDER BY AthleteName ASC LIMIT $start, $limit;";

        $query = $this->db->query($query);

        if ($query->rowCount() > 0) {
            $result = $query->fetchAll();
            $this->results = $result;

            return $result;
        }
        return false;
    }

    /**
     * Return a number of random athletes - used for feature athletes.
     *
     * @param int $limit number of athletes to return (Default = 3)
     * @return array|false list of random athletes with aggregate stats
     */
    public function getRandom(int $limit = 3): array
    {
        $query = "SELECT 
                        A.*,
                        COUNT(F.FightID) AS TotalFights,
                        SUM(IF(FR.WinnerAthleteID=A.AthleteID, 1, 0)) AS TotalWins,
                        SUM(IF(RT.ResultDescription='Draw', 1, 0)) AS TotalDraws,
                        SUM(IF(RT.ResultDescription='Decision%', 1, 0)) AS TotalDecisionWins,
                        SUM(IF(RT.ResultDescription='Submission', 1, 0)) AS TotalSubmissions,
                        A.randId

                    FROM 
                        (SELECT *, RAND() AS randId FROM Athletes) AS A
                        LEFT JOIN FightAthletes FA on A.AthleteID = FA.AthleteID
                        LEFT JOIN Fights F ON F.FightID = FA.FightID
                        LEFT JOIN WeightClasses WC on F.WeightClassID = WC.WeightClassID
                        LEFT JOIN Referees R on F.RefereeID = R.RefereeID
                        LEFT JOIN FightResults FR on F.FightID = FR.FightID
                        LEFT JOIN ResultTypes RT on FR.ResultTypeID = RT.ResultTypeID
                    GROUP BY A.AthleteID
                        HAVING COUNT(F.FightID) > 5
                    ORDER BY A.randId DESC
                    LIMIT $limit;";

        $query = $this->db->query($query);

        if ($query->rowCount() > 0) {
            $result = $query->fetchAll();
            $this->results = $result;

            return $result;
        }
        return false;
    }

    /**
     * Retrieves the total records in the database.
     *
     * @return int total number of athlete records
     */
    public function getTotal(): int
    {
        $query = $this->db->query("SELECT * FROM Athletes");
        return $query->rowCount();
    }


    /**
     * Retrieves the total number of fights an athlete has competed in.
     *
     * @return int total number of fights a specified athlete has had
     */
    public function getAthleteTotalFights(int $id): int
    {
        $query = $this->db->query("SELECT * FROM FightAthletes WHERE AthleteID=$id");
        return $query->rowCount();
    }

    /**
     * Create a new athlete entry in the database
     *
     * @param array|null $data should contain AthleteName, AthleteHeightInCM, AthleteReachInCM, AthleteStanceID, AthleteDOB
     * @return int number of records created
     */
    public function create(array $data): int
    {
        $this->processData($data);
        $this->validateData();

        $query = "INSERT INTO Athletes 
                    (AthleteName, AthleteHeightInCM, AthleteReachInCM, AthleteStanceID, AthleteDOB)
                    VALUES (?, ?, ?, ?, ?);";

        $query = $this->db->prepare($query);
        $query->execute([$this->name, $this->height, $this->reach, $this->stanceId, $this->dob]);

        $this->setAthleteId($this->db->lastInsertId());

        return $query->rowCount();
    }

    /**
     * Updates database record for the specified athlete.
     *
     * @param int $id the athlete id
     * @param array $data should contain AthleteName, AthleteHeightInCM, AthleteReachInCM, AthleteStanceID, AthleteDOB
     * @return int number of records updated
     */
    public function update(int $id, array $data): int
    {
        $this->setAthleteId($id);
        $this->processData($data);
        $this->validateData();

        $query = "UPDATE 
                        Athletes 
                    SET 
                        AthleteName = ?, 
                        AthleteHeightInCM = ?, 
                        AthleteReachInCM = ?, 
                        AthleteStanceID = ?, 
                        AthleteDOB = ?
                WHERE 
                        AthleteID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->name, $this->height, $this->reach, $this->stanceId, $this->dob, $this->athleteId]);

        return $query->rowCount();
    }

    /**
     * Delete the specified record from the database.
     *
     * @param int $id of the record to be deleted
     * @return int the number of rows deleted
     */
    public function delete(int $id): int
    {
        $this->setAthleteId($id);

        $query = "DELETE FROM Athletes WHERE AthleteID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->athleteId]);

        return $query->rowCount();
    }

    /**
     * Extracts inputs from data array and calls setters. If any data is not in the expected format
     * exceptions will be thrown from the relevant setter.
     *
     * @param array $data
     */
    private function processData(array $data): void
    {
        $this->setDob($data['AthleteDOB'] ?? '');
        $this->setName($data['AthleteName'] ?? '');
        $this->setStanceId($data['AthleteStanceID'] ?? 0);
        $this->setReach($data['AthleteReachInCM'] ?? 0);
        $this->setHeight($data['AthleteHeightInCM'] ?? 0);
    }

    /**
     * Checks that all record fields have been populated. If not, throws InvalidArgumentException.
     */
    private function validateData(): void
    {
        if (is_null($this->name) || is_null($this->height) || is_null($this->reach) || is_null($this->stanceId)
            || is_null($this->dob)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    // getters and setters

    /**
     * @return int|null
     */
    public function getAthleteId(): ?int
    {
        return $this->athleteId;
    }

    /**
     * @param int $athleteId
     */
    public function setAthleteId(int $athleteId): void
    {
        if ($athleteId <= 0) {
            throw new InvalidArgumentException("Invalid value for AthleteID. ");
        }

        $this->athleteId = $athleteId;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        if (strlen($name) < self::NAME_MIN_LENGTH || strlen($name) > self::NAME_MAX_LENGTH) {
            throw new InvalidArgumentException("Invalid value for AthleteName. " .
                "Name length must be between " . self::NAME_MIN_LENGTH . "-" . self::NAME_MAX_LENGTH . " characters");
        }

        $this->name = $name;
    }

    /**
     * @return float|null
     */
    public function getHeight(): ?float
    {
        return $this->height;
    }

    /**
     * @param float|null $height
     */
    public function setHeight(?float $height): void
    {
        if ($height < self::HEIGHT_MIN || $height > self::HEIGHT_MAX) {
            throw new InvalidArgumentException("Invalid value for AthleteHeightInCM. " .
                "Height must be between " . self::HEIGHT_MIN . "-" . self::HEIGHT_MAX . " cm");
        }
        $this->height = floatval($height);
    }

    /**
     * @return float|null
     */
    public function getReach(): ?float
    {
        return $this->reach;
    }

    /**
     * @param float|null $reach
     */
    public function setReach(?float $reach): void
    {
        if ($reach < self::REACH_MIN || $reach > self::REACH_MAX) {
            throw new InvalidArgumentException("Invalid value for AthleteReachInCM. " .
                "Reach must be between " . self::REACH_MIN . "-" . self::REACH_MAX . " cm");
        }
        $this->reach = floatval($reach);
    }

    /**
     * @return int|null
     */
    public function getStanceId(): ?int
    {
        return $this->stanceId;
    }

    /**
     * @param int|null $stanceId
     */
    public function setStanceId(?int $stanceId): void
    {
        if ($stanceId <= 0) {
            throw new InvalidArgumentException("Invalid value for StanceID.");
        }
        // make sure record exists
        $stance = (new AthleteStance($this->db))->getOne($stanceId);
        if (!$stance) {
            throw new InvalidArgumentException("No record exists with the specified StanceID");
        }

        $this->stanceId = intval($stanceId);
    }

    /**
     * @return string|null
     */
    public function getDob(): ?string
    {
        return $this->dob;
    }

    /**
     * @param string|null $dob
     */
    public function setDob(?string $dob): void
    {
        if (!strtotime($dob)) {
            throw new InvalidArgumentException("Invalid value for AthleteDOB.");
        }

        $this->dob = date("Y-m-d", strtotime($dob));
    }

    /**
     * @return null
     */
    public function getResults()
    {
        return $this->results;
    }

}
