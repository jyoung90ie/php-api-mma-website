<?php

namespace models;

use InvalidArgumentException;
use PDOException;

class Referee
{
    const PERMISSION_AREA = 'FIGHTS';
    private $refereeId = null;
    private $refereeName = null;
    private $results;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Return data for specified referee id.
     *
     * @param int $id
     * @return false
     */
    public function getOne(int $id)
    {
        $this->setRefereeId($id);

        $query = "SELECT * FROM Referees WHERE RefereeID=?";


        $query = $this->db->prepare($query);
        $query->execute([$this->refereeId]);

        if ($query->rowCount() > 0) {

            $result = $query->fetch();

            $this->refereeName = $result['RefereeName'];

            return $result;
        }
        return false;

    }

    /**
     * Returns a list of Referee's alphabetically ordered by first name - results are paginated.
     *
     * @param int $limit the number of records to return
     * @param int $start first record to return from
     * @return mixed list of referees if successful, otherwise, return false
     */
    public function getAll(int $limit = 5, int $start = 0): array
    {
        $query = "SELECT * FROM Referees ORDER BY RefereeName LIMIT $start, $limit";
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
        $query = $this->db->query("SELECT * FROM Referees");
        return $query->rowCount();
    }


    public function create(array $data): bool
    {
        $this->processData($data);
        $this->validateData();

        $query = "INSERT INTO Referees (RefereeName) VALUES (?);";

        $query = $this->db->prepare($query);
        $query->execute([$this->refereeName]);

        $this->refereeId = $this->db->lastInsertId();

        return $query->rowCount();
    }

    public function update(int $id, array $data): bool
    {
        $this->setRefereeId($id);
        $this->processData($data);
        $this->validateData();

        $query = "UPDATE Referees 
                    SET 
                        RefereeName = ?
                    WHERE 
                          RefereeID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->refereeName, $this->refereeId]);

        return $query->rowCount();
    }

    public function delete(int $id): bool
    {
        $this->setRefereeId($id);

        $this->validateIdSet();

        $query = "DELETE FROM Referees WHERE RefereeID=?";

        $query = $this->db->prepare($query);
        $query->execute([$this->refereeId]);

        return $query->rowCount();
    }

    // utility functions
    private function processData(array $data)
    {
        $this->setRefereeName($data['RefereeName'] ?? '');
    }

    private function validateData(): void
    {
        if (is_null($this->refereeName)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    private function validateIdSet(): void
    {
        if (!isset($this->refereeId)) {
            throw new InvalidArgumentException("Object Id has no value");
        }
    }

    // getters and setters

    /**
     * @return int
     */
    public function getRefereeId(): ?int
    {
        return $this->refereeId;
    }

    /**
     * @param int $refereeId
     */
    public function setRefereeId(int $refereeId): void
    {
        if ($refereeId <= 0) {
            throw new InvalidArgumentException("Invalid RefereeID");
        }
        $this->refereeId = $refereeId;
    }

    /**
     * @return string
     */
    public function getRefereeName(): ?string
    {
        return $this->refereeName;
    }

    /**
     * @param string $refereeName
     */
    public function setRefereeName(string $refereeName): void
    {
        if (empty($refereeName)) {
            throw new InvalidArgumentException('RefereeName must have a value');
        }
        $this->refereeName = $refereeName;
    }

    /**
     * @return
     */
    public function getResults()
    {
        return $this->results;
    }
}