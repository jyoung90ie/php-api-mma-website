<?php

namespace models;

use InvalidArgumentException;
use PDOException;

/**
 * Class RolePermission
 * @package models
 */
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

    /**
     * Retrieve all role permission records.
     *
     * @return mixed database results
     */
    public function getAll()
    {
        $query = "SELECT * FROM " . self::TABLE;
        $query = $this->db->query($query);

        $result = $query->fetchAll();
        $this->results = $result;

        return $result;
    }

    /**
     * Create new role permission records. This creates multiple records for each role, dependent on the number of
     * permissions the role has assigned.
     *
     * @param array $data this should contain an array for each permission,
     *  e.g. [ [RoleID=1, Permission=1], [RoleID=1, PermissionID=2], ..., [RoleID=1, PermissionID=N] ]
     * @return int number of records created
     */
    public function create(array $data): int
    {
        $this->setPermissions($data);
        $this->setRoleId($data[0]['RoleID'] ?? '');

        $insert_values = "";
        // build insert values
        foreach ($this->permissions as $permission) {
            $insert_values .= "($this->roleId, $permission), ";
        }

        // remove the last comma
        $insert_values = rtrim($insert_values, ', ');

        $query = "INSERT INTO RolePermissions
                    (RoleID, PermissionID) 
                VALUES $insert_values";
        $query = $this->db->query($query);

        return $query->rowCount();
    }

    /**
     * Placeholder function so that CRUDController can handle any requests.
     *
     * @param int|null $roleId
     * @param array|null $data
     * @return array
     */
    public function update(?int $roleId, ?array $data): array
    {
        return ['Error' => 'It is not possible to update a RolePermission.'];
    }

    /**
     * Delete all records from RolePermissions table which have the RoleID specified.
     *
     * @param int $roleId to remove all associated records from RolePermissions
     * @return int number of rows deleted
     */
    public function delete(int $roleId): int
    {
        $this->setRoleId($roleId);

        $query = "DELETE FROM RolePermissions WHERE RoleId = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->roleId]);

        return $query->rowCount();
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
                throw new InvalidArgumentException("Invalid value for PermissionID");
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