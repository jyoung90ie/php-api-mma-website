<?php

namespace models;

use InvalidArgumentException;
use PDOException;

class Referee
{
    const PERMISSION_AREA = 'FIGHTS';
    private $id = null;
    private $name = null;
    private $results;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        $this->setId($id);

        $query = "SELECT * FROM Referees WHERE RefereeID=?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->id]);

            $result = $query->fetch();

            $this->name = $result['RefereeName'];

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * Returns a list of Referee's alphabetically ordered by first name.
     *
     * @return mixed list of referees if successful, otherwise, return false
     */
    public function getAll()
    {
        $query = "SELECT * FROM Referees ORDER BY RefereeName";
        try {
            $query = $this->db->query($query);

            if ($query->rowCount() > 0) {
                $result = $query->fetchAll();

                $this->results = $result;

                return $result;
            }
            return false;
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
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


    public function create(): bool
    {
        $this->validateData();

        $query = "INSERT INTO Referees (RefereeName) VALUES (?);";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->name]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function update(): bool
    {
        $this->validateData();
        $this->validateIdSet();

        $query = "UPDATE Referees 
                    SET 
                        RefereeName = ?
                    WHERE 
                          RefereeID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->name, $this->id]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function delete(): bool
    {
        $this->validateIdSet();

        $query = "DELETE FROM Referees WHERE RefereeID=?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->id]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    // utility functions
    private function validateData(): void
    {
        if (is_null($this->name)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    private function validateIdSet(): void
    {
        if (!isset($this->id)) {
            throw new InvalidArgumentException("Object Id has no value");
        }
    }

    // getters and setters

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("Invalid Referee ID");
        }
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return
     */
    public function getResults()
    {
        return $this->results;
    }
}