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
    private $results = null;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        $this->setFightResultId($id);

        $query = "SELECT * FROM FightResults WHERE FightResultID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->fightResultId]);

        if ($query->rowCount() > 0) {
            $result = $query->fetch();

            $this->fightId = $result['FightID'];
            $this->resultTypeId = $result['ResultTypeID'];
            $this->winnerId = $result['WinnerAthleteID'];

            return $result;
        }

        return false;

    }

    public function getByFight(int $fightId)
    {
        if (!is_numeric($fightId)) {
            throw new InvalidArgumentException("Invalid Fight ID");
        }

        $this->setFightId($fightId);

        $query = "SELECT * FROM FightResults WHERE FightID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->fightId]);

        $result = $query->fetch();

        $this->fightResultId = $result['FightResultID'];
        $this->resultTypeId = $result['ResultTypeID'];
        $this->winnerId = $result['WinnerAthleteID'];

        $this->results = $result;

        return $result;
    }

    /**
     * Return list of fight results sorted by ID in descending order - results are limited to 5 records by default.
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


    public function create(array $data): bool
    {
        $this->processData($data);
        $this->validateData();

        $query = "INSERT INTO FightResults 
                    (FightID, ResultTypeID, WinnerAthleteID)
                    VALUES (?, ?, ?);";

        $query = $this->db->prepare($query);
        $query->execute([$this->fightId, $this->resultTypeId, $this->winnerId]);

        if ($query->rowCount() > 0) {
            $this->fightResultId = $this->db->lastInsertedId();

            return $query->rowCount();
        }

        return false;
    }

    public function update(int $id, array $data): bool
    {
        $this->setFightId($id);
        $this->processData($data);
        $this->validateData();

        $query = "UPDATE FightResults 
                    SET 
                        FightID = ?, 
                        ResultTypeID = ?, 
                        WinnerAthleteID = ?
                    WHERE 
                        FightResultID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->fightId, $this->resultTypeId, $this->winnerId, $this->fightResultId]);

        return $query->rowCount();
    }

    public function delete(int $id): bool
    {
        $this->setFightResultId($id);

        $query = "DELETE FROM FightResults WHERE FightResultID=$this->fightResultId";

        $query = $this->db->prepare($query);
        $query->execute([$this->fightResultId]);

        return $query->rowCount();
    }

    // utility functions
    private function validateData(): void
    {
        if (is_null($this->fightId) || is_null($this->resultTypeId) || is_null($this->winnerId)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    function processData(array $data): void
    {
        $this->setFightId($data['FightID']);
        $this->setWinnerId($data['WinnerAthleteID']);
        $this->setResultTypeId($data['ResultTypeID']);
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
            throw new InvalidArgumentException("Invalid ID");
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
            throw new InvalidArgumentException("Invalid Fight ID");
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
            throw new InvalidArgumentException("Invalid Result Type ID");
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
            throw new InvalidArgumentException("Invalid winner Athlete ID");
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
}