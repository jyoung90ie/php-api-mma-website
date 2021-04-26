<?php

namespace models;

use Exception;
use InvalidArgumentException;
use PDOException;

class Role
{
    const TABLE = "Roles";

    private $roleId = null;
    private $description = null;

    private $results;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        // performs validation checks before setting
        $this->setRoleId($id);

        $query = "SELECT * FROM Roles WHERE RoleID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->roleId]);

            if ($query->rowCount() > 0) {
                return $query->fetch();
            }

            return false;
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function getAll()
    {
        $query = "SELECT * FROM Roles";

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

        $query = "INSERT INTO Roles (RoleDescription) VALUES (?)";

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

        $query = "UPDATE 
                    Roles
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

        $query = "DELETE FROM Roles WHERE RoleID=?";

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