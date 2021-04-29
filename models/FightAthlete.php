<?php

namespace models;

use InvalidArgumentException;

class FightAthlete
{
    const PERMISSION_AREA = 'FIGHTS';
    const DATA_FIELDS = [
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
    private $fightAthleteId = null;
    private $fightId = null;
    private $athleteId = null;
    private $results = null;
    private $dataFields;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->dataFields = self::DATA_FIELDS;
    }

    /**
     * Return the specified fight athlete record
     *
     * @param int $fightAthleteId for the records you wish to return
     * @return mixed return fightAthlete record if successful, otherwise false
     */
    public function getOne(int $fightAthleteId)
    {
        $this->setFightAthleteId($fightAthleteId);

        $query = "SELECT 
                        *
                    FROM 
                        FightAthletes 
                    WHERE
                        FightAthleteID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->fightAthleteId]);

        if ($query->rowCount() > 0) {
            $results = $query->fetchAll();

            $this->results = $results;

            return $results;
        }

        return false;
    }

    /**
     * Return the fight athlete records for the associated fightId
     *
     * @param int $fightId for the records you wish to return
     * @return mixed return fightAthlete records associated with fightId if successful, otherwise false
     */
    public function getByFightId(int $fightId)
    {
        $this->setFightId($fightId);

        $query = "SELECT 
                        FA.*,
                        A.AthleteName,
                        F.NumOfRounds
                    FROM 
                        FightAthletes FA
                    LEFT JOIN Athletes A on A.AthleteID = FA.AthleteID
                    LEFT JOIN Fights F on FA.FightID = F.FightID
                    WHERE FA.FightID = ?";

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
     * @return array|false all fight athletes (limited to pagination amount)
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
     * @param array|null $data must contain FightID and AthleteID
     * @return int
     */
    public function create(array $data): int
    {
        $this->processData($data);
        $this->validateData();

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

    /**
     * Updates specified fightAthlete record. This loops through the data array and ensures that the fields specified
     * in the instance var dataFields all exist, and are valid numbers greater than or equal to zero.
     *
     * If any fields are missing or invalid, an InvalidArgumentException is thrown.
     *
     * @param int $fightAthleteId for the record for the record to be updated
     * @param array $data must contain all fields specified in dataFields instance var.
     * @return int number of records updated
     */
    public function update(int $fightAthleteId, array $data): int
    {
        $this->setFightAthleteId($fightAthleteId);

        $validationErrors = '';

        foreach ($this->dataFields as $field) {
            if (!isset($data[$field]) || (empty($data[$field]) && !($data[$field] >= 0))) {
                $validationErrors .= "$field must have a value. \n";
            } elseif (!is_numeric($data[$field]) || !(intval($data[$field]) >= 0)) {
                $validationErrors .= "$field must be a number (greater than or equal to zero). \n";
            }

            $data[$field] = intval($data[$field]);
        }

        if (!empty($validationErrors)) {
            throw new InvalidArgumentException($validationErrors);
        }

        $params = [
            ':fightAthleteId' => $this->fightAthleteId
        ];

        // loop through fields and create additional updateQuery commands and params
        $updateFields = "";
        foreach ($this->dataFields as $field) {
            $updateFields .= "$field=:$field, ";
            $params[':' . $field] = $data[$field];
        }

        // remove trailing comma
        $updateFields = rtrim($updateFields, ', ');

        $query = "UPDATE 
                        FightAthletes
                    SET 
                        $updateFields
                    WHERE 
                        FightAthleteID=:fightAthleteId";

        $query = $this->db->prepare($query);
        $query->execute($params);

        return $query->rowCount();
    }

    /**
     * Delete the specified record from the database.
     *
     * @param int $fightAthleteId of the record to be deleted
     * @return int the number of rows deleted
     */
    public function delete(int $fightAthleteId): int
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
     * Extracts inputs from data array and calls setters. If any data is not in the expected format
     * exceptions will be thrown from the relevant setter.
     *
     * NOTE: This is only called for create() function. The update() function validates data internally.
     *
     * @param array $data must contain FightID and AthleteID
     */
    private function processData(array $data): void
    {
        $this->setFightId($data['FightID'] ?? 0);
        $this->setAthleteId($data['AthleteID'] ?? 0);
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
            throw new InvalidArgumentException("Invalid value for AthleteID");
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
            throw new InvalidArgumentException("Invalid value for AthleteID");
        }

        // make sure record exists
        $athlete = (new Athlete($this->db))->getOne($athleteId);
        if (!$athlete) {
            throw new InvalidArgumentException("No record exists with the specified AthleteID");
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