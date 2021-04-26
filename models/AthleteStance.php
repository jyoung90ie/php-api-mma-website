<?php

namespace models;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;

class AthleteStance
{

    private $id = null;
    private $description = null;
    private $results;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        $this->setId($id);

        $query = "SELECT * FROM AthleteStances WHERE AthleteStanceID=?";
        $query = $this->db->prepare($query);
        $query->execute([$this->id]);



        if ($query->rowCount() > 0) {
            $result = $query->fetch();
            $this->description = $result['StanceDescription'];

        } else {
            $this->results = null;
        }

        return $this->results;
    }

    public function getAll()
    {
        $query = "SELECT * FROM AthleteStances";
        $query = $this->db->query($query);

        if ($query->rowCount() > 0) {
            $results = $query->fetchAll();

            $this->results = $results;

            return $results;
        } else {
            $this->results = null;
        }

        return false;
    }


    public function create(?string $description = null): int
    {
        if (!is_null($description)) {
            $this->setDescription($description);
        }
        $this->validateData();

        $query = "INSERT INTO AthleteStances (StanceDescription)
                    VALUES (?);";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->description]);

            $this->id = $this->db->lastInsertId();

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function update(int $id, ?string $description = null): int
    {
        $this->setId($id);

        if (!is_null($description)) {
            $this->setDescription($description);
        }

        $this->validateData();

        $query = "UPDATE AthleteStances SET StanceDescription=?
                WHERE AthleteStanceID=?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->description, $this->id]);

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        $this->setId($id);

        $query = "DELETE FROM AthleteStances WHERE AthleteStanceID=?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->id]);

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    // utility functions
    private function validateData(): void
    {
        if (is_null($this->description)) {
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
            throw new InvalidArgumentException("Invalid Athlete Stance ID");
        }
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getResults()
    {
        return $this->results;
    }
}