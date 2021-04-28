<?php

namespace models;

use Exception;
use InvalidArgumentException;
use TypeError;

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

    public function getOne(int $id)
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
                FROM Fights F 
                LEFT JOIN FightAthletes FA on F.FightID = FA.FightID
                LEFT JOIN Athletes A on FA.AthleteID = A.AthleteID
                LEFT JOIN WeightClasses WC on F.WeightClassID = WC.WeightClassID
                LEFT JOIN Referees R on F.RefereeID = R.RefereeID
                LEFT JOIN FightResults FR on F.FightID = FR.FightID
                LEFT JOIN ResultTypes RT on FR.ResultTypeID = RT.ResultTypeID
                WHERE A.AthleteID = ?
                GROUP BY A.AthleteID";

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

    public function getOneOLD(int $id)
    {
        $this->setAthleteId($id);

        $query = "SELECT * FROM Athletes WHERE AthleteID = ?";

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

            // get athlete fights
            $query = "SELECT 
                                E.EventID,
                                E.EventLocation,
                                E.EventDate,
                                FA.FightID,
                                F.WeightClassID,
                                F.TitleBout,
                                F.NumOfRounds,
                                (CASE 
                                    WHEN FR.WinnerAthleteID=FA.AthleteID THEN 'Won'
                                    WHEN NOT(ISNULL(FR.WinnerAthleteID)) THEN 'Lost'                                   
                                    WHEN ISNULL(FR.WinnerAthleteID) THEN 'Draw'
                                    ELSE 'Other' END
                                ) AS Outcome
                            FROM FightAthletes FA
                            LEFT JOIN FightResults FR on FA.FightID = FR.FightID
                            LEFT JOIN Fights F ON FA.FightID = F.FightID
                            LEFT JOIN Events E ON F.EventID = E.EventID
                            WHERE FA.AthleteID = ?
                            ORDER BY E.EventDate DESC";
            $query = $this->db->prepare($query);
            $query->execute([$this->athleteId]);

            $athlete_data = $athlete;
            $athlete_data['Fights'] = [];

            if ($query->rowCount() > 0) {
                $fights = $query->fetchAll();
                array_push($athlete_data['Fights'], $fights);
            }

            return $athlete_data;
        }

        return false;
    }

    /**
     * Return list of athletes sorted by name in ascending order - results are limited to 5 records by default.
     *
     * @param int $limit the number of athletes to return
     * @param int $start first record to return from
     * @return array|false
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
     * Retrieves the total records in the database.
     *
     * @return int total number of records
     */
    public function getTotal(): int
    {
        $query = $this->db->query("SELECT * FROM Athletes");
        return $query->rowCount();
    }

    /**
     * @param array|null $data
     * @return int
     */
    public function create(?array $data): int
    {
        if (!is_null($data)) {
            $this->processData($data);
        }

        $query = "INSERT INTO Athletes 
                    (AthleteName, AthleteHeightInCM, AthleteReachInCM, AthleteStanceID, AthleteDOB)
                    VALUES (?, ?, ?, ?, ?);";

        $query = $this->db->prepare($query);
        $query->execute([$this->name, $this->height, $this->reach, $this->stanceId, $this->dob]);

        $this->setAthleteId($this->db->lastInsertId());

        return $query->rowCount();
    }

    public function update(int $id, ?array $data = null): int
    {
        $this->setAthleteId($id);

        if (!is_null($data)) {
            $this->processData($data);
        }
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

    public function delete(int $id): int
    {
        $this->setAthleteId($id);

        $query = "DELETE FROM Athletes WHERE AthleteID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->athleteId]);

        return $query->rowCount();
    }

    // utility functions
    private function processData(array $data): void
    {
        try {
            $this->setDob($data['AthleteDOB']);
            $this->setName($data['AthleteName']);
            $this->setStanceId($data['AthleteStanceID']);
            $this->setReach($data['AthleteReachInCM']);
            $this->setHeight($data['AthleteHeightInCM']);
        } catch (Exception | TypeError $exception) {
            exit($exception->getMessage());
        }
    }

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
            $this->athleteId = -1;
        } else {
            $this->athleteId = $athleteId;
        }
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
            throw new InvalidArgumentException("Name length must be between " . self::NAME_MIN_LENGTH . "-" .
                self::NAME_MAX_LENGTH . " characters");
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
            throw new InvalidArgumentException("Height must be between " . self::HEIGHT_MIN . "-" .
                self::HEIGHT_MAX . " cm");
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
            throw new InvalidArgumentException("Reach must be between " . self::REACH_MIN . "-" .
                self::REACH_MAX . " cm");
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
            throw new InvalidArgumentException("Invalid date for DOB");
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

$sql = " 
SELECT 
	A.AthleteID,
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
	

FROM Fights F 
LEFT JOIN FightAthletes FA on F.FightID = FA.FightID
LEFT JOIN Athletes A on FA.AthleteID = A.AthleteID
LEFT JOIN WeightClasses WC on F.WeightClassID = WC.WeightClassID
LEFT JOIN Referees R on F.RefereeID = R.RefereeID
LEFT JOIN FightResults FR on F.FightID = FR.FightID
LEFT JOIN ResultTypes RT on FR.ResultTypeID = RT.ResultTypeID
WHERE A.AthleteID = 2673
";