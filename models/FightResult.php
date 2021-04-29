<?php

namespace models;

use InvalidArgumentException;

class FightResult
{
    const PERMISSION_AREA = 'FIGHTS';

    private $fightResultId = null;
    private $fightId = null;
    private $resultTypeId = null;
    private $winnerId = null;
    private $winRound = null;
    private $winRoundTime = null;
    private $results = null;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Returns the fight results for the specified fight.
     *
     * @param int $fightId the fight id
     * @return array|false fight result data if successful, otherwise false.
     */
    public function getOne(int $fightId)
    {
        $this->setFightId($fightId);

        $query = "SELECT * FROM FightResults WHERE FightID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->fightId]);

        if ($query->rowCount() > 0) {
            $result = $query->fetch();

            $this->fightResultId = $result['FightResultID'];
            $this->resultTypeId = $result['ResultTypeID'];
            $this->winnerId = $result['WinnerAthleteID'];

            return $result;
        }

        return false;

    }

    /**
     * Return list of fight results sorted by FightResultID in descending order - results are limited to 5 records by default.
     *
     * @param int $limit the number of athletes to return
     * @param int $start first record to return from
     * @return array|false
     */
    public function getAll(int $limit = 5, int $start = 0): array
    {
        $query = "SELECT * FROM FightResults ORDER BY FightResultID DESC LIMIT $start, $limit;";

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
        $query = $this->db->query("SELECT * FROM Fights");
        return $query->rowCount();
    }

    /**
     * Create a new fight result entry in the database.
     *
     * @param array $data should contain FightID, ResultTypeID, WinnerAthleteID, WinRound and WinRoundTime
     * @return int number of records created
     */
    public function create(array $data): int
    {
        $this->setFightId($data['FightID'] ?? 0); // needs to be called separately due to update taking it as a parameter
        $this->processData($data);
        $this->validateData();

        $query = "INSERT INTO FightResults 
                    (FightID, ResultTypeID, WinnerAthleteID, WinRound, WinRoundTime)
                    VALUES (?, ?, ?, ?, ?);";

        $query = $this->db->prepare($query);
        $query->execute([
            $this->fightId,
            $this->resultTypeId,
            $this->winnerId,
            $this->winRound,
            $this->winRoundTime
        ]);

        $rowCount = $query->rowCount();

        if ($rowCount > 0) {
            $this->fightResultId = $this->db->lastInsertId();

        }
        return $rowCount;
    }

    /**
     * Updates the fight result record for the specified fight.
     *
     * @param int $fightId that corresponds to the fight result to be updated
     * @param array $data should contain FightID, ResultTypeID, WinnerAthleteID, WinRound and WinRoundTime
     * @return int number of records updated
     */
    public function update(int $fightId, array $data): int
    {
        $this->setFightId($fightId);
        $this->processData($data);
        $this->validateData();

        $query = "UPDATE FightResults 
                    SET 
                        ResultTypeID = ?, 
                        WinnerAthleteID = ?,
                        WinRound = ?,
                        WinRoundTime = ?
                    WHERE 
                        FightID = ?";

        $query = $this->db->prepare($query);
        $query->execute([
            $this->resultTypeId,
            $this->winnerId,
            $this->winRound,
            $this->winRoundTime,
            $this->fightId
        ]);

        return $query->rowCount();
    }

    /**
     * Deletes the fight result record for the specified id
     * @param int $fightResultId the id of the specific result to be deleted
     * @return int number of records deleted
     */
    public function delete(int $fightResultId): int
    {
        $this->setFightResultId($fightResultId);

        $query = "DELETE FROM FightResults WHERE FightResultID=$this->fightResultId";

        $query = $this->db->prepare($query);
        $query->execute([$this->fightResultId]);

        return $query->rowCount();
    }

    // utility functions
    /**
     * Extracts inputs from data array and calls setters. If any data is not in the expected format
     * exceptions will be thrown from the relevant setter.
     *
     * @param array $data
     */
    function processData(array $data): void
    {
        $this->setWinnerId($data['WinnerAthleteID'] ?? 0);
        $this->setResultTypeId($data['ResultTypeID'] ?? 0);
        $this->setWinRound($data['WinRound'] ?? -1);
        $this->setWinRoundTime($data['WinRoundTime'] ?? '');
    }

    private function validateData(): void
    {
        if (is_null($this->fightId) || is_null($this->resultTypeId) || is_null($this->winnerId)
            || is_null($this->winRoundTime) || is_null($this->winRound)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    // getters and setters

    /**
     * @return int
     */
    public function getFightResultId(): ?int
    {
        return $this->fightResultId;
    }

    /**
     * @param int $fightResultId
     */
    public function setFightResultId(int $fightResultId): void
    {
        if ($fightResultId <= 0) {
            throw new InvalidArgumentException("Invalid value for FightResultID");
        }
        $this->fightResultId = $fightResultId;
    }

    /**
     * @return int|null
     */
    public function getFightId(): ?int
    {
        return $this->fightId;
    }

    /**
     * @param int $fightId
     */
    public function setFightId(int $fightId): void
    {
        if ($fightId <= 0) {
            throw new InvalidArgumentException("Invalid value for FightID");
        }

        // make sure fight exists
        $fight = (new Fight($this->db))->getOne($fightId);
        if (!$fight) {
            throw new InvalidArgumentException("No record exists with the specified FightID");
        }

        $this->fightId = $fightId;
    }

    /**
     * @return int|null
     */
    public function getResultTypeId(): ?int
    {
        return $this->resultTypeId;
    }

    /**
     * @param int $resultTypeId
     */
    public function setResultTypeId(int $resultTypeId): void
    {
        if ($resultTypeId <= 0) {
            throw new InvalidArgumentException("Invalid value for ResultTypeID");
        }

        // make sure record exists
        $resultType = (new ResultType($this->db))->getOne($resultTypeId);
        if (!$resultType) {
            throw new InvalidArgumentException("No record exists with the specified ResultTypeID");
        }

        $this->resultTypeId = $resultTypeId;
    }

    /**
     * @return int|null
     */
    public function getWinnerId(): ?int
    {
        return $this->winnerId;
    }

    /**
     * @param int $winnerId
     */
    public function setWinnerId(int $winnerId): void
    {
        if ($winnerId <= 0) {
            throw new InvalidArgumentException("Invalid value for WinnerAthleteID");
        }

        // make sure record exists
        $athlete = (new Athlete($this->db))->getOne($winnerId);
        if (!$athlete) {
            throw new InvalidArgumentException("No record exists with the specified WinnerAthleteID");
        }

        $this->winnerId = $winnerId;
    }

    /**
     * @return null
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @return null
     */
    public function getWinRoundTime()
    {
        return $this->winRoundTime;
    }

    /**
     * @param string $winRoundTime
     */
    public function setWinRoundTime(string $winRoundTime): void
    {
        if (!isset($winRoundTime) || empty($winRoundTime) ||
            !preg_match('/(^[0]?[0-5][:][0-5][0-9])/', $winRoundTime)) {
            throw new InvalidArgumentException("Invalid value for WinRoundTime - must have a value in the format M:SS");
        }
        $this->winRoundTime = $winRoundTime;
    }

    /**
     * @return null
     */
    public function getWinRound()
    {
        return $this->winRound;
    }

    /**
     * @param int $winRound
     */
    public function setWinRound(int $winRound): void
    {
        if (!is_numeric($winRound) || $winRound > 5 || $winRound < 1) {
            throw new InvalidArgumentException("Invalid value for WinRound - must be a number between 1 and 5");
        }
        $this->winRound = intval($winRound);
    }
}