<?php

namespace models;

use Exception;
use InvalidArgumentException;
use PDOException;

/**
 * Class Role
 * @package models
 */
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

    /**
     * Retrieve single role record.
     *
     * @param int $id role id
     * @return mixed database result or false
     */
    public function getOne(int $id)
    {
        $this->setRoleId($id);

        $query = "SELECT * FROM Roles WHERE RoleID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->roleId]);

        $rowCount = $query->rowCount();

        if ($rowCount) {
            return $query->fetch();
        }

        return false;
    }

    /**
     * Retrieve all role records.
     *
     * @return mixed database records
     */
    public function getAll()
    {
        $query = "SELECT * FROM Roles";

        $query = $this->db->query($query);

        $result = $query->fetchAll();
        $this->results = $result;

        return $result;
    }

    /**
     * Create new role record.
     *
     * @param array $data must contain RoleDescription
     * @return int number of records created
     */
    public function create(array $data): int
    {
        $this->setDescription($data['RoleDescription'] ?? '');
        $this->validateData();

        $query = "INSERT INTO Roles (RoleDescription) VALUES (?)";

        $query = $this->db->prepare($query);
        $query->execute([$this->description]);

        $this->roleId = $this->db->lastInsertId();

        return $query->rowCount();
    }

    /**
     * Update a single role record.
     *
     * @param int $id role id
     * @param array $data must contain RoleDescription
     * @return int number of rows updated
     */
    public function update(int $id, array $data): int
    {
        $this->setRoleId($id);
        $this->setDescription($data['RoleDescription'] ?? '');

        $this->validateData();

        $query = "UPDATE 
                    Roles
                SET 
                    RoleDescription = ?
                WHERE 
                    RoleID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->description, $this->roleId]);

        return $query->rowCount();
    }

    public function delete(int $id): int
    {
        $this->setRoleId($id);

        $query = "DELETE FROM Roles WHERE RoleID=?";

        $query = $this->db->prepare($query);
        $query->execute([$this->roleId]);

        return $query->rowCount();
    }

    /**
     * Checks that all record fields have been populated. If not, throws InvalidArgumentException.
     */
    private function validateData(): void
    {
        if (is_null($this->description)) {
            throw new InvalidArgumentException("All object variables must have a value");
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
            throw new InvalidArgumentException("Invalid value for RoleID");
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
        if (empty($description)) {
            throw new InvalidArgumentException('Invalid value for RoleDescription');
        }
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