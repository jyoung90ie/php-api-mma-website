<?php

namespace models;

use InvalidArgumentException;
use PDOException;

class WeightClass
{
    const PERMISSION_AREA = 'FIGHTS';
    const WEIGHT_IN_LBS_MIN = 100;
    const WEIGHT_IN_LBS_MAX = 500;

    private $id = null;
    private $weight_class = null;
    private $min_weight = null;
    private $max_weight = null;
    private $results;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        $this->setId($id);

        $query = "SELECT * FROM WeightClasses WHERE WeightClassID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->id]);

            $result = $query->fetch();
            $this->results = $result;

            $this->weight_class = $result['WeightClass'];
            $this->min_weight = $result['MinWeightInLB'];
            $this->max_weight = $result['MaxWeightInLB'];

            return $result;
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * Return list of weight classes, ordered by ascending maximum permitted weight for each class.
     *
     * @return array|false
     */
    public function getAll(): array
    {
        $query = "SELECT * FROM WeightClasses ORDER BY MaxWeightInLB ASC";
        try {
            $query = $this->db->query($query);

            if ($query->rowCount() > 0) {

                $result = $query->fetchAll();
                $this->results = $result;

                return $result;
            }
            return false;
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * Retrieves the total records in the database.
     *
     * @return int total number of records
     */
    public function getTotal(): int
    {
        $query = $this->db->query("SELECT * FROM WeightClasses");
        return $query->rowCount();
    }


    public function create(): int
    {
        $this->validateData();

        $query = "INSERT INTO WeightClasses 
                    (WeightClass, MinWeightInLB, MaxWeightInLB)
                    VALUES (?, ?, ?);";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->weight_class, $this->min_weight, $this->max_weight]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function update(): int
    {
        $this->validateData();
        $this->validateIdSet();

        $query = "UPDATE WeightClasses 
                    SET 
                        WeightClass = ?,
                        MinWeightInLB = ?, 
                        MaxWeightInLB = ?
                    WHERE 
                        WeightClassID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->weight_class, $this->min_weight, $this->max_weight, $this->id]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function delete(): bool
    {
        $this->validateIdSet();

        $query = "DELETE FROM WeightClasses WHERE WeightClassID = ?";

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
        if (is_null($this->weight_class) || is_null($this->min_weight) || is_null($this->max_weight)) {
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
            throw new InvalidArgumentException("Invalid Weight Class ID");
        }
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getWeightClass(): ?string
    {
        return $this->weight_class;
    }

    /**
     * @param string $weight_class
     */
    public function setWeightClass(string $weight_class): void
    {
        $this->weight_class = $weight_class;
    }

    /**
     * @return int|null
     */
    public function getMinWeight(): ?int
    {
        return $this->min_weight;
    }

    /**
     * @param int $min_weight
     */
    public function setMinWeight(int $min_weight): void
    {
        if (self::WEIGHT_IN_LBS_MIN > $min_weight) {
            throw new InvalidArgumentException("Minimum weight must be at least " . self::WEIGHT_IN_LBS_MIN);
        }

        $this->min_weight = $min_weight;
    }

    /**
     * @return int|null
     */
    public function getMaxWeight(): ?int
    {
        return $this->max_weight;
    }

    /**
     * @param int $max_weight
     */
    public function setMaxWeight(int $max_weight): void
    {
        if (self::WEIGHT_IN_LBS_MAX < $max_weight) {
            throw new InvalidArgumentException("Maximum weight must not exceed " . self::WEIGHT_IN_LBS_MAX);
        }

        $this->max_weight = $max_weight;
    }

    /**
     * @return mixed
     */
    public function getResults()
    {
        return $this->results;
    }
}