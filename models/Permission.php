<?php

namespace models;

use InvalidArgumentException;
use PDO;
use PDOException;

class Permission
{
    const AREAS = ['FIGHTS', 'ATHLETES', 'USERS', 'COMMENTS', 'ROLES'];
    const TYPES = ['CREATE', 'READ', 'UPDATE', 'DELETE', 'ASSIGN'];

    const DESCRIPTION_MIN = 5;
    const DESCRIPTION_MAX = 100;


    const TABLE = "Permissions";

    private ?int $id = null;
    private ?string $area = null;
    private ?string $type = null;
    private ?string $description = null;
    private $results = null;

    private PDO $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        $this->setId($id);

        $query = "SELECT * FROM Permissions WHERE PermissionID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$id]);

            $result = $query->fetch();
            $this->results = $result;

            $this->area = $result['PermissionArea'];
            $this->type = $result['PermissionType'];
            $this->description = $result['PermissionDescription'];

            return $result;
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }


    public function getAll()
    {
        $query = "SELECT * FROM Permissions";
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

        $query = "INSERT INTO Permissions
                    (PermissionArea, PermissionType, PermissionDescription) 
                    VALUES (?, ?, ?);";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->area, $this->type, $this->description]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function update(): int
    {
        $this->validateData();
        $this->validateIdSet();

        $query = "UPDATE Permissions
                    SET 
                        PermissionDescription = ?, 
                        PermissionArea = ?, 
                        PermissionType = ? 
                    WHERE 
                        PermissionID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->description, $this->area, $this->type, $this->id]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function delete(): int
    {
        $this->validateIdSet();

        $query = "DELETE FROM Permissions WHERE PermissionID = ?";

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
        if (is_null($this->description) || is_null($this->area) || is_null($this->type)) {
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
            throw new InvalidArgumentException("Invalid ID");
        }
        $this->id = $id;
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
            throw new InvalidArgumentException("Permission Area must be one of the following: " .
                implode(",", self::AREAS));
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
            throw new InvalidArgumentException("Permission Type must be one of the following: " .
                implode(",", self::TYPES));
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
            throw new InvalidArgumentException("Password must be between " . self::DESCRIPTION_MIN . "-" .
                self::DESCRIPTION_MAX . " characters long");
        }
        $this->description = $description;
    }

    public function getResults()
    {
        return $this->results;
    }
}