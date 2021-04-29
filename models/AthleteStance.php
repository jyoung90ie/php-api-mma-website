<?php

namespace models;

use InvalidArgumentException;

class AthleteStance
{

    const PERMISSION_AREA = 'ATHLETES';
    private $stanceId = null;
    private $stanceDescription = null;
    private $results;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Returns data on specified athlete stance.
     *
     * @param int $id the athlete stance id
     * @return array|false single athlete stance if successful
     */
    public function getOne(int $id)
    {
        $this->setStanceId($id);

        $query = "SELECT * FROM AthleteStances WHERE AthleteStanceID=?";

        $query = $this->db->prepare($query);
        $query->execute([$this->stanceId]);

        if ($query->rowCount() > 0) {

            $result = $query->fetch();

            $this->stanceDescription = $result['StanceDescription'];

            return $result;
        }
        return false;
    }

    /**
     * Returns a list of stance descriptions in ascending order - results are paginated by default.
     *
     * @param int $limit the number of records to return
     * @param int $start first record to return
     * @return mixed list of stances if successful, otherwise, return false
     */
    public function getAll(int $limit = 5, int $start = 0)
    {
        $query = "SELECT * FROM AthleteStances ORDER BY StanceDescription LIMIT $start, $limit;";
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
     * @return int total number of stance records
     */
    public function getTotal(): int
    {
        $query = $this->db->query("SELECT * FROM AthleteStances");
        return $query->rowCount();
    }

    /**
     * Create a new athlete stance record in the database
     *
     * @param array|null $data should contain StanceDescription
     * @return int number of records created
     */
    public function create(array $data): int
    {
        $this->processData($data);
        $this->validateData();

        $query = "INSERT INTO AthleteStances (StanceDescription) VALUES (?);";

        $query = $this->db->prepare($query);
        $query->execute([$this->stanceDescription]);

        $this->stanceId = $this->db->lastInsertId();

        return $query->rowCount();
    }

    /**
     * Updates database record for the specified athlete stance.
     *
     * @param int $id the athlete stance id
     * @param array $data should contain StanceDescription
     * @return int number of records updated
     */
    public function update(int $id, array $data): int
    {
        $this->setStanceId($id);
        $this->processData($data);
        $this->validateData();

        $query = "UPDATE AthleteStances 
                    SET 
                        StanceDescription = ?
                    WHERE 
                        AthleteStanceID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->stanceDescription, $this->stanceId]);

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
        $this->setStanceId($id);

        $query = "DELETE FROM AthleteStances WHERE AthleteStanceID=?";

        $query = $this->db->prepare($query);
        $query->execute([$this->stanceId]);

        return $query->rowCount();
    }

    /**
     * Extracts inputs from data array and calls setters. If any data is not in the expected format
     * exceptions will be thrown from the relevant setter.
     *
     * @param array $data
     */
    private function processData(array $data)
    {
        $this->setStanceDescription($data['StanceDescription'] ?? '');
    }

    /**
     * Checks that all record fields have been populated. If not, throws
     * InvalidArgumentException.
     */
    private function validateData(): void
    {
        if (is_null($this->stanceDescription)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    // getters and setters

    /**
     * @return int
     */
    public function getStanceId(): ?int
    {
        return $this->stanceId;
    }

    /**
     * @param int $stanceId
     */
    public function setStanceId(int $stanceId): void
    {
        if ($stanceId <= 0) {
            throw new InvalidArgumentException("Invalid AthleteStanceID");
        }
        $this->stanceId = $stanceId;
    }

    /**
     * @return string
     */
    public function getStanceDescription(): ?string
    {
        return $this->stanceDescription;
    }

    /**
     * @param string $stanceDescription
     */
    public function setStanceDescription(string $stanceDescription): void
    {
        if (empty($stanceDescription)) {
            throw new InvalidArgumentException('StanceDescription must have a value');
        }
        $this->stanceDescription = $stanceDescription;
    }

    /**
     * @return mixed
     */
    public function getResults()
    {
        return $this->results;
    }
}