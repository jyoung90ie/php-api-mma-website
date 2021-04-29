<?php

namespace models;

use InvalidArgumentException;

class ResultType
{
    const PERMISSION_AREA = 'FIGHTS';
    private $resultTypeId = null;
    private $resultDescription = null;
    private $results;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Return a single result type record from the database
     * @param int $id result type id
     * @return mixed database record or false
     */
    public function getOne(int $id)
    {
        $this->setResultTypeId($id);

        $query = "SELECT * FROM ResultTypes WHERE ResultTypeID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->resultTypeId]);

        if ($query->rowCount() > 0) {
            $result = $query->fetch();

            $this->resultTypeId = $result['ResultTypeID'];
            $this->resultDescription = $result['ResultDescription'];

            return $result;
        }

        return false;
    }

    /**
     * Return list of all fight outcomes
     * @return array|false
     */
    public function getAll(): array
    {
        $query = "SELECT * FROM ResultTypes ORDER BY ResultDescription ASC";

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
        $query = $this->db->query("SELECT * FROM ResultTypes");
        return $query->rowCount();
    }

    /**
     * Creates a new result type and stores it in the database.
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {

        $this->processData($data);

        $query = "INSERT INTO ResultTypes 
                    (ResultDescription)
                VALUES 
                    (?);";

        $query = $this->db->prepare($query);
        $query->execute([$this->resultDescription]);

        $this->setResultTypeId($this->db->lastInsertId());

        return $query->rowCount();
    }

    public function update(int $id, array $data = null): int
    {
        $this->setResultTypeId($id);
        $this->processData($data);

        $this->validateData();

        $query = "UPDATE 
                        ResultTypes 
                    SET 
                        ResultDescription = ?
                WHERE 
                        ResultTypeID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->resultDescription, $this->resultTypeId]);

        return $query->rowCount();
    }

    public function delete(int $id): int
    {
        $this->setResultTypeId($id);
        $query = "DELETE FROM ResultTypes WHERE ResultTypeID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->resultTypeId]);

        return $query->rowCount();
    }

    // utility functions
    private function validateData(): void
    {
        if (is_null($this->resultDescription)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    private function processData(array $data): void
    {
        $this->setResultDescription($data['ResultDescription'] ?? '');
    }

    // getters and setters

    /**
     * @return int
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

        $this->resultTypeId = $resultTypeId;
    }

    /**
     * @return string
     */
    public function getResultDescription(): ?string
    {
        return $this->resultDescription;
    }

    /**
     * @param string $resultDescription
     */
    public function setResultDescription(string $resultDescription): void
    {
        if (empty($resultDescription)) {
            throw new InvalidArgumentException('Invalid value for ResultDescription');
        }

        $this->resultDescription = $resultDescription;
    }

    /**
     * @return mixed
     */
    public function getResults()
    {
        return $this->results;
    }
}