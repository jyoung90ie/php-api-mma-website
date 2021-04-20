<?php

namespace models;

use PDO;
use PDOException;

class RolePermission
{
    const TABLE = "RolePermissions";

    private ?int $permission_id = null;
    private ?int $role_id = null;
    private ?array $permissions = null;

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


    public function create(): bool
    {

        $insert_values = "";

        // build insert values
        foreach ($this->permissions as $permission) {
            $insert_values .= "($this->role_id, $permission), ";
        }

        // remove the last comma
        $insert_values = rtrim($insert_values, ', ');

        $query = "INSERT INTO " . self::TABLE . " (RoleID, PermissionID) VALUES $insert_values";

        try {
            $query = $this->db->query($query);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function update(): bool
    {
        $this->validateData();
        $this->validateIdSet();

        $query = "UPDATE " . self::TABLE . " 
                SET 
                    RoleID = ?, 
                    PermissionID = ?
                WHERE 
                    RoleID=? AND PermissionID=?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->role_id, $this->permissions, $this->role_id, $this->permission_id]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function delete(): bool
    {
        $this->validateIdSet();

        $query_counter = 0;

        $query = "DELETE FROM " . self::TABLE . " WHERE RoleID=? AND PermissionID=?";

        foreach ($this->permissions as $permission) {
            $query = $this->db->prepare($query);
            $query->execute([$this->role_id, $permission]);

            if ($query->rowCount() > 0) {
                $query_counter++;
            }
        }

        if ($query_counter == sizeof($this->permissions)) {
            return true;
        }

        return false;
    }

    // utility functions
    private function validateData(): void
    {
        if (is_null($this->role_id) || is_null($this->permissions)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    private function validateIdSet(): void
    {
        if (!isset($this->role_id) || !isset($this->permissions)) {
            throw new InvalidArgumentException("Missing value for Role ID and/or Permission ID");
        }
    }

    // getters and setters

    /**
     * @return int
     */
    public function getRoleId(): ?int
    {
        return $this->role_id;
    }

    /**
     * @param int $role_id
     */
    public function setRoleId(int $role_id): void
    {
        if (!is_int($role_id) || $role_id <= 0) {
            throw new InvalidArgumentException("Invalid Role ID");
        }
        $this->role_id = $role_id;
    }

    /**
     * @return array|null
     */
    public function getPermissions(): ?array
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     */
    public function setPermissions(array $permissions): void
    {
        foreach ($permissions as $permission) {
            if (!is_int($permission) || $permission <= 0) {
                throw new InvalidArgumentException("Invalid Permission ID");
            }
        }
        $this->permissions = $permissions;
    }

    /**
     * @return
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @return int|null
     */
    public function getPermissionId(): ?int
    {
        return $this->permission_id;
    }

    /**
     * @param int|null $permission_id
     */
    public function setPermissionId(?int $permission_id): void
    {
        if (!is_int($permission_id) || $permission_id <= 0) {
            throw new InvalidArgumentException("Invalid Permission ID");
        }
        $this->permission_id = $permission_id;
    }
}