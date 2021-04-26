<?php

namespace models;

use InvalidArgumentException;
use PDOException;

class RolePermission
{
    const TABLE = "RolePermissions";

    private $permissionId = null;
    private $roleId = null;
    private $permissions = null;

    private $results;
    private $db;

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
            $insert_values .= "($this->roleId, $permission), ";
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
            $query->execute([$this->roleId, $this->permissions, $this->roleId, $this->permissionId]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function delete(): bool
    {
        $this->validateIdSet();

        $query_counter = 0;


        foreach ($this->permissions as $permission) {
            $query = "DELETE FROM " . self::TABLE . " WHERE RoleID=? AND PermissionID=?";
            $query = $this->db->prepare($query);
            $query->execute([$this->roleId, $permission]);

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
        if (is_null($this->roleId) || is_null($this->permissions)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    private function validateIdSet(): void
    {
        if (!isset($this->roleId) || !isset($this->permissions)) {
            throw new InvalidArgumentException("Missing value for Role ID and/or Permission ID");
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
        if (!is_int($roleId) || $roleId <= 0) {
            throw new InvalidArgumentException("Invalid Role ID");
        }
        $this->roleId = $roleId;
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
        return $this->permissionId;
    }

    /**
     * @param int|null $permissionId
     */
    public function setPermissionId(?int $permissionId): void
    {
        if (!is_int($permissionId) || $permissionId <= 0) {
            throw new InvalidArgumentException("Invalid Permission ID");
        }
        $this->permissionId = $permissionId;
    }
}