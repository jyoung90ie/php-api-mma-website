<?php

namespace models;

use InvalidArgumentException;

class WeightClass
{
    const PERMISSION_AREA = 'FIGHTS';
    const WEIGHT_IN_LBS_MIN = 100;
    const WEIGHT_IN_LBS_MAX = 500;

    private $weightClassId = null;
    private $weightClass = null;
    private $minWeightInLB = null;
    private $maxWeightInLB = null;
    private $results;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Retrieve single weight class record
     * @param int $id weight class id
     * @return mixed database results or false
     */
    public function getOne(int $id)
    {
        $this->setWeightClassId($id);

        $query = "SELECT * FROM WeightClasses WHERE WeightClassID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->weightClassId]);

        $rowCount = $query->rowCount();
        if ($rowCount > 0) {
            $result = $query->fetch();
            $this->results = $result;

            $this->weightClass = $result['WeightClass'];
            $this->minWeightInLB = $result['MinWeightInLB'];
            $this->maxWeightInLB = $result['MaxWeightInLB'];

            return $result;
        }

        return false;
    }

    /**
     * Return list of weight classes, ordered by ascending maximum permitted weight for each class.
     *
     * @return array|false
     */
    public function getAll(): array
    {
        $query = "SELECT * FROM WeightClasses ORDER BY MaxWeightInLB";
        $query = $this->db->query($query);

        if ($query->rowCount() > 0) {

            $result = $query->fetchAll();
            $this->results = $result;

            return $result;
        }
        return false;
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


    public function create(array $data): int
    {
        $this->processData($data);
        $this->validateData();

        $query = "INSERT INTO WeightClasses 
                    (WeightClass, MinWeightInLB, MaxWeightInLB)
                    VALUES (?, ?, ?);";

        $query = $this->db->prepare($query);
        $query->execute([$this->weightClass, $this->minWeightInLB, $this->maxWeightInLB]);

        return $query->rowCount();
    }

    /**
     * Update single weight class record.
     *
     * @param int $weightClassId weight class id
     * @param array $data must contain WeightClass, MinWeightInLB, and MaxWeightInLB
     * @return int the number of rows updated
     */
    public function update(int $weightClassId, array $data): int
    {
        $this->setWeightClassId($weightClassId);
        $this->processData($data);
        $this->validateData();

        $query = "UPDATE WeightClasses 
                    SET 
                        WeightClass = ?,
                        MinWeightInLB = ?, 
                        MaxWeightInLB = ?
                    WHERE 
                        WeightClassID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->weightClass, $this->minWeightInLB, $this->maxWeightInLB, $this->weightClassId]);

        return $query->rowCount();
    }

    public function delete(int $weightClassId): bool
    {
        $this->setWeightClassId($weightClassId);
        $query = "DELETE FROM WeightClasses WHERE WeightClassID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->weightClassId]);

        return $query->rowCount();

    }

// utility functions
    private function validateData(): void
    {
        if (is_null($this->weightClass) || is_null($this->minWeightInLB) || is_null($this->maxWeightInLB)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    private function processData(array $data): void
    {
        $this->setWeightClass($data['WeightClass'] ?? '');
        $this->setMinWeightInLB($data['MinWeightInLB'] ?? 0);
        $this->setMaxWeightInLB($data['MaxWeightInLB'] ?? 0);
    }

// getters and setters

    /**
     * @return int
     */
    public
    function getWeightClassId(): ?int
    {
        return $this->weightClassId;
    }

    /**
     * @param int $id
     */
    public
    function setWeightClassId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("Invalid Weight Class ID");
        }
        $this->weightClassId = $id;
    }

    /**
     * @return string
     */
    public function getWeightClass(): string
    {
        return $this->weightClass;
    }

    /**
     * @param string $weightClass
     */
    public function setWeightClass(string $weightClass): void
    {
        if (empty($weightClass)) {
            throw new InvalidArgumentException('Invalid value for WeightClass');
        }
        $this->weightClass = $weightClass;
    }

    /**
     * @return int|null
     */
    public function getMinWeightInLB(): int
    {
        return $this->minWeightInLB;
    }

    /**
     * @param int $minWeightInLB
     */
    public function setMinWeightInLB(int $minWeightInLB): void
    {
        if (self::WEIGHT_IN_LBS_MIN > $minWeightInLB) {
            throw new InvalidArgumentException("Invalid value for MinWeightInLB. Minimum weight must be at least " . self::WEIGHT_IN_LBS_MIN);
        }

        $this->minWeightInLB = $minWeightInLB;
    }

    /**
     * @return int|null
     */
    public
    function getMaxWeightInLB(): ?int
    {
        return $this->maxWeightInLB;
    }

    /**
     * @param int $maxWeightInLB
     */
    public
    function setMaxWeightInLB(int $maxWeightInLB): void
    {
        if ($maxWeightInLB < $this->minWeightInLB || $maxWeightInLB > self::WEIGHT_IN_LBS_MAX) {
            throw new InvalidArgumentException("Invalid value for MaxWeightInLB. Maximum weight must be between " .
                $this->minWeightInLB . "-" . self::WEIGHT_IN_LBS_MAX . " pounds");
        }
        $this->maxWeightInLB = $maxWeightInLB;
    }

    /**
     * @return mixed
     */
    public
    function getResults()
    {
        return $this->results;
    }
}