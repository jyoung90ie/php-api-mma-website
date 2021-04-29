<?php

namespace models;

use InvalidArgumentException;
use PDOException;

class Permission
{
    const AREAS = ['FIGHTS', 'ATHLETES', 'USERS', 'COMMENTS', 'ROLES'];
    const TYPES = ['CREATE', 'READ', 'UPDATE', 'DELETE', 'ASSIGN'];

    const DESCRIPTION_MIN = 5;
    const DESCRIPTION_MAX = 100;

    const TABLE = "Permissions";

    private $permissionId = null;
    private $area = null;
    private $type = null;
    private $description = null;
    private $results = null;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Retrieve single permission record from the database.
     *
     * @param int $id permission id
     * @return mixed database record or false
     */
    public function getOne(int $id)
    {
        $this->setPermissionId($id);

        $query = "SELECT * FROM Permissions WHERE PermissionID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$id]);

        if ($query->rowCount() > 0) {
            $result = $query->fetch();
            $this->results = $result;

            $this->area = $result['PermissionArea'];
            $this->type = $result['PermissionType'];
            $this->description = $result['PermissionDescription'];

            return $result;
        }
        return false;
    }


    /**
     * Retrieve all permission records.
     *
     * @return mixed
     */
    public function getAll()
    {
        $query = "SELECT * FROM Permissions";
        $query = $this->db->query($query);

        $result = $query->fetchAll();
        $this->results = $result;
        return $result;
    }

    /**
     * Create new permission record.
     *
     * @param array $data must contain PermissionArea, PermissionType, and PermissionDescription
     * @return int number of records created
     */
    public function create(array $data): int
    {
        $this->processData($data);
        $this->validateData();

        $query = "INSERT INTO Permissions
                    (PermissionArea, PermissionType, PermissionDescription) 
                    VALUES (?, ?, ?);";

        $query = $this->db->prepare($query);
        $this->permissionId = $this->db->lastInsertId();

        $query->execute([$this->area, $this->type, $this->description]);

        return $query->rowCount();
    }

    /**
     * Update an existing permission record.
     *
     * @param int $id permission id
     * @param array $data must contain PermissionArea, PermissionType, and PermissionDescription
     * @return int number of records updated
     */
    public function update(int $id, array $data): int
    {
        $this->setPermissionId($id);
        $this->processData($data);
        $this->validateData();

        $query = "UPDATE Permissions
                    SET 
                        PermissionDescription = ?, 
                        PermissionArea = ?, 
                        PermissionType = ? 
                    WHERE 
                        PermissionID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->description, $this->area, $this->type, $this->permissionId]);

        return $query->rowCount();
    }

    /**
     * Delete a single permission record.
     *
     * @param int $id permission id
     * @return int number of records deleted
     */
    public function delete(int $id): int
    {
        $this->setPermissionId($id);

        $query = "DELETE FROM Permissions WHERE PermissionID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->permissionId]);

        return $query->rowCount();
    }

    /**
     * Extracts inputs from data array and calls setters. If any data is not in the expected format
     * exceptions will be thrown from the relevant setter.
     *
     * @param array $data
     */
    private function processData(array $data): void
    {
        $this->setDescription($data['PermissionDescription'] ?? '');
        $this->setType($data['PermissionType'] ?? '');
        $this->setArea($data['PermissionArea'] ?? '');
    }

    /**
     * Checks that all record fields have been populated. If not, throws InvalidArgumentException.
     */
    private function validateData(): void
    {
        if (is_null($this->description) || is_null($this->area) || is_null($this->type)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    /**
     * @return int
     */
    public function getPermissionId(): ?int
    {
        return $this->permissionId;
    }

    /**
     * @param int $permissionId
     */
    public function setPermissionId(int $permissionId): void
    {
        if ($permissionId <= 0) {
            throw new InvalidArgumentException("Invalid PermissionID");
        }
        $this->permissionId = $permissionId;
    }

    /**
     * @return string|null
     */
    public function getArea(): ?string
    {
        return $this->area;
    }

    /**
     * @param string|null $area
     */
    public function setArea(?string $area): void
    {
        if (!in_array($area, self::AREAS)) {
            throw new InvalidArgumentException("Invalid value for PermissionArea. " .
                "Permission Area must be one of the following: " . implode(",", self::AREAS));
        }
        $this->area = $area;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        if (!in_array($type, self::TYPES)) {
            throw new InvalidArgumentException("Invalid value for PermissionType. " .
                "Permission Type must be one of the following: " . implode(",", self::TYPES));
        }
        $this->type = $type;
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
        if (strlen($description) < self::DESCRIPTION_MIN || strlen($description) > self::DESCRIPTION_MAX) {
            throw new InvalidArgumentException("Invalid value for PermissionDescription. " .
                "Description must be between " . self::DESCRIPTION_MIN . "-" . self::DESCRIPTION_MAX . " characters long");
        }
        $this->description = $description;
    }

    public function getResults()
    {
        return $this->results;
    }
}