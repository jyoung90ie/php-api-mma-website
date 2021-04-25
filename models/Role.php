<?php

namespace models;

use InvalidArgumentException;
use PDO;
use PDOException;

class Role
{
    const TABLE = "Roles";

    private ?int $roleId = null;
    private ?string $description = null;

    private $results;
    private PDO $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . self::TABLE;

        try {
            $query = $this->db->query($query);

            $result = $query->fetchAll();
            $this->results = $result;

            return $result;
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }


    public function create(): int
    {
        $this->validateData();

        $query = "INSERT INTO " . self::TABLE . " (RoleDescription) VALUES (?)";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->description]);

            $this->roleId = $this->db->lastInsertId();

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function update(): int
    {
        $this->validateData();
        $this->validateIdSet();

        $query = "UPDATE " . self::TABLE . " 
                SET 
                    RoleDescription = ?
                WHERE 
                    RoleID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->description, $this->roleId]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function delete(): int
    {
        $this->validateIdSet();

        $query = "DELETE FROM " . self::TABLE . " WHERE RoleID=?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->roleId]);

            return $query->rowCount();
        } catch (PDOException $exception) {
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
        if (!isset($this->roleId)) {
            throw new InvalidArgumentException("Object Id has no value");
        }
    }

    // getters and setters

    /**
     * @return int
     */
    public function getRoleId(): ?int
    {
        return $this->roleId;
    }

    /**
     * @param int $roleId
     */
    public function setRoleId(int $roleId): void
    {
        if ($roleId <= 0) {
            throw new InvalidArgumentException("Invalid ID");
        }
        $this->roleId = $roleId;
    }

    /**
     * @return string|null
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

    /**
     * @return
     */
    public function getResults()
    {
        return $this->results;
    }
}