<?php

namespace models;

use InvalidArgumentException;

class FightAthlete
{
    const PERMISSION_AREA = 'FIGHTS';
    private $fightAthleteId = null;
    private $fightId = null;
    private $athleteId = null;
    private $results = null;
    private $dataFields;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->dataFields = [
            'stats_strikesThrown',
            'stats_strikesLanded',
            'stats_significantStrikesThrown',
            'stats_significantStrikesLanded',
            'stats_takedownsThrown',
            'stats_takedownsLanded',
            'stats_submissionAttempts',
            'stats_knockDowns',
            'stats_positionReversals'
        ];
    }

    /**
     * Return the fight athlete records for the associated fight
     *
     * @param int $fightId for the records you wish to return
     * @return false
     */
    public function getOne(int $fightId)
    {
        $this->setFightId($fightId);

        $query = "SELECT * FROM FightAthletes WHERE FightID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->fightId]);

        if ($query->rowCount() > 0) {
            $results = $query->fetchAll();

            $this->results = $results;

            return $results;
        }

        return false;
    }

    /**
     * Return list of fight athletes sorted by FightID in descending order - results are limited to 5 records by default.
     *
     * @param int $limit the number of records to return
     * @param int $start first record to return from
     * @return array|false
     */
    public function getAll(int $limit = 5, int $start = 0): array
    {
        $query = "SELECT * FROM FightAthletes ORDER BY FightID DESC LIMIT $start, $limit;";

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
        $query = $this->db->query("SELECT * FROM FightAthletes");
        return $query->rowCount();
    }

    /**
     * Creates a new fight athlete and stores it in the database.
     *
     * @param array|null $data
     * @return int
     */
    public function create(?array $data): int
    {
        if (!is_null($data)) {
            $this->processData($data);
        }

        $query = "INSERT INTO FightAthletes 
                    (FightID, AthleteID)
                VALUES 
                    (:fightId, :athleteId);";

        $query = $this->db->prepare($query);
        $params = [
            ':fightId' => $this->fightId,
            ':athleteId' => $this->athleteId
        ];
        $query->execute($params);

        if ($query->rowCount() > 0) {
            $this->setFightAthleteId($this->db->lastInsertId());
        }

        return $query->rowCount();
    }

    public function update(int $fightAthleteId, array $data): int
    {
        $this->setFightAthleteId($fightAthleteId);

        if (!is_null($data)) {
            $this->processData($data, true);
        }

        $this->validateData();

        $params = [
            ':fightId' => $this->fightId,
            ':athleteId' => $this->athleteId,
            ':fightAthleteId' => $this->fightAthleteId
        ];

        // loop through fields and create additional updateQuery commands and params
        $updateFields = "";
        foreach ($this->dataFields as $field) {
            $updateFields .= "$field=:$field, ";
            $params[$updateFields] = [$field => $data[$field]];
        }

        // remove trailing comma
        $updateFields = rtrim($updateFields, ',');

        $query = "UPDATE 
                        FightAthletes
                    SET 
                        FightID = :fightId, AthleteID = :athleteId,
                        $updateFields
                    WHERE 
                        FightAthleteID = :fightAthleteId";

        $query = $this->db->prepare($query);

        $query->execute($params);

        return $query->rowCount();
    }

    public function delete(int $fightAthleteId): bool
    {
        $this->setFightAthleteId($fightAthleteId);

        $query = "DELETE FROM FightAthletes WHERE FightAthleteID=:fightAthleteId";

        $query = $this->db->prepare($query);
        $params = [':fightAthleteId' => $this->fightAthleteId];

        $query->execute($params);

        return $query->rowCount();
    }

    // utility functions
    private function validateData(): void
    {
        if (is_null($this->fightId) || is_null($this->athleteId)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    /**
     * @param array $data
     * @param false $update set to true if this is being run for update query
     */
    private function processData(array $data, $update = false): void
    {
        $this->setFightId($data['FightID']);
        $this->setAthleteId($data['AthleteID']);

        $validationErrors = '';

        foreach ($this->dataFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $validationErrors .= "$field must have a value. \n";
            } elseif (!is_numeric($data[$field])) {
                $validationErrors .= "$field must be a number. \n";
            }

            $data[$field] = intval($data[$field]);
        }

        if (!empty($validationErrors)) {
            throw new InvalidArgumentException($validationErrors);
        }
    }

    // getters and setters

    /**
     * @return int
     */
    public function getFightAthleteId(): ?int
    {
        return $this->fightAthleteId;
    }

    /**
     * @param int $fightAthleteId
     */
    public function setFightAthleteId(int $fightAthleteId): void
    {
        if ($fightAthleteId <= 0) {
            throw new InvalidArgumentException("Invalid ID");
        }
        $this->fightAthleteId = $fightAthleteId;
    }

    /**
     * @return string
     */
    public function getFightId(): ?string
    {
        return $this->fightId;
    }

    /**
     * @param string $fightId
     */
    public function setFightId(string $fightId): void
    {
        if ($fightId <= 0) {
            throw new InvalidArgumentException("Invalid Fight ID");
        }
        $this->fightId = $fightId;
    }

    /**
     * @return string
     */
    public function getAthleteId(): ?string
    {
        return $this->athleteId;
    }

    /**
     * @param string $athleteId
     */
    public function setAthleteId(string $athleteId): void
    {
        if ($athleteId <= 0) {
            throw new InvalidArgumentException("Invalid Athlete ID");
        }
        $this->athleteId = $athleteId;
    }

    /**
     * @return
     */
    public function getResults()
    {
        return $this->results;
    }
}