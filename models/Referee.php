<?php

namespace models;

use PDO;
use PDOException;

class Referee
{
    private ?int $id = null;
    private ?string $name = null;
    private $results;

    private PDO $db;
    private string $table = "Referees";

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        $this->setId($id);

        $query = "SELECT * FROM $this->table WHERE RefereeID=?";

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

    public function getAll()
    {
        $query = "SELECT * FROM $this->table";
        try {
            $query = $this->db->query($query);
            $result = $query->fetchAll();

            $this->results = $result;

            return $result;
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }


    public function create(): bool
    {
        $this->validateData();

        $query = "INSERT INTO $this->table (RefereeName) VALUES (?);";

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

        $query = "UPDATE $this->table 
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

        $query = "DELETE FROM $this->table WHERE RefereeID=?";

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