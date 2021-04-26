<?php

namespace models;

use InvalidArgumentException;
use PDOException;

class APIAccess
{
    const TABLE = "ApiAccess";

    private $id = null;
    private $apiKey = null;
    private $startDate = null;
    private $endDate = null;
    private $userId = null;
    private $verified = false;

    private $db;
    static $table = "APIAccess";

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Determines whether the apiKey is valid or not. If it is valid, the object instance vars are set based on
     * results from the database.
     *
     * @param string $apiKey - value to be verified
     * @return mixed - true if valid, false if not, exception if db error
     */
    public function verifyKey(string $apiKey)
    {
        $now = date('Y-m-d');

        $query = "SELECT * FROM ApiAccess
                    WHERE 
                        ApiKey=?
                    AND 
                        (StartDate <= ? OR StartDate IS NULL) AND (EndDate >= ? OR EndDate IS NULL)";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$apiKey, $now, $now]);

            if ($query->rowCount() > 0) {
                $result = $query->fetch();


                $start_date = $result['StartDate'];
                $end_date = $result['EndDate'];
                $user_id = $result['UserID'];

                $this->id = $result['ID'];
                $this->apiKey = $result['ApiKey'];
                $this->startDate = (is_null($start_date)) ? "" : $start_date;
                $this->endDate = (is_null($end_date)) ? "" : $end_date;
                $this->userId = (is_null($user_id)) ? -1 : $user_id;
                $this->verified = true;

                return $result;
            }

            return false;
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function create(): int
    {
        $this->validateData();

        $query = "INSERT INTO ApiAccess (ApiKey, StartDate, EndDate, UserID)
                    VALUES(?, ?, ?, ?);";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->apiKey, $this->startDate, $this->endDate, $this->userId]);

            $this->id = $this->db->lastInsertId();

            return $this->id;
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function update(): int
    {
        $this->validateData();
        $this->validateIdSet();

        $query = "UPDATE 
                        ApiAccess
                    SET 
                        ApiKey = ?, 
                        StartDate = ?, 
                        EndDate = ?, 
                        UserID = ?
                    WHERE 
                        ID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->apiKey, $this->startDate, $this->endDate, $this->userId]);
            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }

    }

    public function delete(): int
    {
        $this->validateIdSet();

        $query = "DELETE FROM ApiAccess WHERE ID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->id]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }


    }

    /**
     * @return string
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     */
    public function setStartDate(string $startDate): void
    {
        if (!$this->isDate($startDate)) {
            throw new InvalidArgumentException("Invalid format for start date");
        }

        if (!is_null($this->endDate) && $startDate >= $this->endDate) {
            throw new InvalidArgumentException("Start date must be before end date");
        }

        $this->startDate = date('Y-m-d', strtotime($startDate));
    }

    /**
     * @return string
     */
    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    /**
     * @param string $endDate
     */
    public function setEndDate(string $endDate): void
    {
        if (!$this->isDate($endDate)) {
            throw new InvalidArgumentException("Invalid format for end date");
        }

        if (!is_null($this->startDate) && $endDate <= $this->startDate) {
            throw new InvalidArgumentException("End date must be after start date");
        }

        $this->endDate = date('Y-m-d', strtotime($endDate));
    }

    /**
     * @return int
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException("Invalid input for user ID");
        }

        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }


    public function isVerified(): bool
    {
        return $this->verified;
    }

    private function validateData(): void
    {
        if (is_null($this->userId) || is_null($this->apiKey) || is_null($this->startDate) || is_null($this->endDate)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    private function validateIdSet(): void
    {
        if (!isset($this->id)) {
            throw new InvalidArgumentException("Object Id has no value");
        }
    }

    private function isDate(string $date): bool
    {
        if (strtotime($date)) {
            return true;
        }

        return false;
    }
}
