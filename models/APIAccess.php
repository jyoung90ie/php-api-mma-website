<?php

namespace models;

use Exception;
use InvalidArgumentException;
use PDOException;
use TypeError;

class APIAccess
{
    const TABLE = "ApiAccess";

    private $apiId = null;
    private $apiKey = null;
    private $startDate = null;
    private $endDate = null;
    private $userId = null;
    private $verified = false;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Determines whether the apiKey is valid or not. If it is valid, the object instance vars are set based on
     * results from the database.
     *
     * @param string $apiKey - value to be verified
     * @return mixed - array if valid, false if not, exception if db error
     */
    public function verifyKey(string $apiKey)
    {
        $now = date('Y-m-d H:i:s');

        $query = "SELECT * FROM ApiAccess
                    WHERE 
                        ApiKey=?
                    AND 
                        (StartDate <= ? OR StartDate IS NULL) AND (EndDate >= ? OR EndDate IS NULL)";


        $query = $this->db->prepare($query);
        $query->execute([$apiKey, $now, $now]);

        if ($query->rowCount() > 0) {
            $result = $query->fetch();

            $start_date = $result['StartDate'];
            $end_date = $result['EndDate'];
            $user_id = $result['UserID'];

            $this->apiId = $result['ID'];
            $this->apiKey = $result['ApiKey'];
            $this->startDate = (is_null($start_date)) ? "" : $start_date;
            $this->endDate = (is_null($end_date)) ? "" : $end_date;
            $this->userId = (is_null($user_id)) ? -1 : $user_id;
            $this->verified = true;

            return $result;
        }

        // unset values if key was invalid
        $this->apiId = null;
        $this->apiKey = null;
        $this->startDate = null;
        $this->endDate = null;
        $this->userId = null;

        return false;
    }

    /**
     * Create a new entry in the APIAccess table.
     *
     * @param array $data all values must be provided (ApiKey, StartDate, EndDate, UserID)
     * @return int the number of rows created
     */
    public function create(array $data): int
    {
        $this->processData($data);
        $this->validateData();

        $query = "INSERT INTO ApiAccess (ApiKey, StartDate, EndDate, UserID)
                    VALUES(?, ?, ?, ?);";

        $query = $this->db->prepare($query);
        $query->execute([$this->apiKey, $this->startDate, $this->endDate, $this->userId]);

        $rowCount = $query->rowCount();
        if ($rowCount > 0) {
            $this->apiId = $this->db->lastInsertId();
        }

        return $rowCount;
    }

    /**
     * Update specified entry in the database.
     *
     * @param int $id of record to be updated
     * @param array $data all values must be provided (ApiKey, StartDate, EndDate, UserID)
     * @return int the number of rows created
     */
    public function update(int $id, array $data): int
    {
        $this->setApiId($id);
        $this->processData($data);
        $this->validateData();

        $query = "UPDATE 
                        ApiAccess
                    SET 
                        ApiKey = ?, 
                        StartDate = ?, 
                        EndDate = ?, 
                        UserID = ?
                    WHERE 
                        ID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->apiKey, $this->startDate, $this->endDate, $this->userId, $this->apiId]);
        return $query->rowCount();
    }

    /**
     * Delete the specified record from the database.
     *
     * @param int $id of the record to be deleted
     * @return int the number of rows deleted
     */
    public function delete(int $id): int
    {
        $this->setApiId($id);

        $query = "DELETE FROM ApiAccess WHERE ID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->apiId]);

        return $query->rowCount();
    }

    /**
     * Checks that all record fields have been populated. If not, throws
     * InvalidArgumentException.
     */
    private function validateData(): void
    {
        if (is_null($this->userId) || is_null($this->apiKey) || is_null($this->startDate) || is_null($this->endDate)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    /**
     * Verifies that the input string is in the correct date format.
     *
     * @param string $date to be verified
     * @return bool true if valid date, otherwise false
     */
    private function isDate(string $date): bool
    {
        if (strtotime($date)) {
            return true;
        }

        return false;
    }

    /**
     * Extracts inputs from data array and calls setters. If any data is not in the expected format
     * exceptions will be thrown from the relevant setter.
     *
     * @param array $data
     */
    private function processData(array $data): void
    {
        $this->setApiId($data['ApiId'] ?? 0);
        $this->setApiKey($data['ApiKey'] ?? '');
        $this->setStartDate($data['StartDate'] ?? '');
        $this->setEndDate($data['EndDate'] ?? '');
        $this->setUserId($data['UserID'] ?? 0);
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
        if (empty($apiKey)) {
            throw new InvalidArgumentException("API Key must have a value");
        }
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
            throw new InvalidArgumentException("Invalid input for User ID");
        }

        // make sure record exists
        $user = (new User($this->db))->getOne($userId);
        if (!$user) {
            throw new InvalidArgumentException("No record exists with the specified UserID");
        }

        $this->userId = $userId;
    }

    /**
     * @param int $apiId
     */
    public function setApiId(int $apiId): void
    {
        if ($apiId <= 0) {
            throw new InvalidArgumentException("Invalid input for API ID");
        }

        $this->apiId = $apiId;
    }

    /**
     * @return int
     */
    public function getApiId(): ?int
    {
        return $this->apiId;
    }

    /**
     * @return bool value of verified instance variable
     */
    public function isVerified(): bool
    {
        return $this->verified;
    }
}
