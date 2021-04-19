<?php


class APIAccess
{
    const TABLE = "ApiAccess";

    private ?int $id = null;
    private ?string $api_key = null;
    private ?string $start_date = null;
    private ?string $end_date = null;
    private ?int $user_id = null;
    private bool $verified = false;

    private PDO $db;
    static string $table = "APIAccess";

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function verifyKey($api_key)
    {
        $now = date('Y-m-d');

        $query = "SELECT * FROM " . self::TABLE . " 
                    WHERE 
                        ApiKey=?
                    AND 
                        (StartDate <= ? OR StartDate IS NULL) AND (EndDate >= ? OR EndDate IS NULL)";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$api_key, $now, $now]);

            $result = $query->fetch();

            $start_date = $result['StartDate'];
            $end_date = $result['EndDate'];
            $user_id = $result['UserID'];

            $this->id = $result['ID'];
            $this->api_key = $result['ApiKey'];
            $this->start_date = (is_null($start_date)) ? "" : $start_date;
            $this->end_date = (is_null($end_date)) ? "" : $end_date;
            $this->user_id = (is_null($user_id)) ? -1 : $user_id;
            $this->verified = true;

            return $result;
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function create(): int
    {
        $this->validateData();

        $query = "INSERT INTO " . self::TABLE . "(ApiKey, StartDate, EndDate, UserID)
                    VALUES('?', '?', '?', ?);";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->api_key, $this->start_date, $this->end_date, $this->user_id]);

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

        $query = "UPDATE " . self::TABLE . " 
                    SET 
                        ApiKey = '?', StartDate = '?', EndDate = '?', UserID = ?
                WHERE ID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->api_key, $this->start_date, $this->end_date, $this->user_id]);
            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }

    }

    public function delete(): int
    {
        $this->validateIdSet();

        $query = "DELETE FROM " . self::TABLE . " WHERE ID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->id]);

            return $query->rowCount();
        } catch(PDOException $exception) {
            die($exception->getMessage());
        }


    }

    /**
     * @return string
     */
    public function getApiKey(): ?string
    {
        return $this->api_key;
    }

    /**
     * @param string $api_key
     */
    public function setApiKey(string $api_key): void
    {
        $this->api_key = $api_key;
    }

    /**
     * @return string
     */
    public function getStartDate(): ?string
    {
        return $this->start_date;
    }

    /**
     * @param string $start_date
     */
    public function setStartDate(string $start_date): void
    {
        if (!$this->isDate($start_date)) {
            throw new InvalidArgumentException("Invalid format for start date");
        }

        if (!is_null($this->end_date) && $start_date >= $this->end_date) {
            throw new InvalidArgumentException("Start date must be before end date");
        }

        $this->start_date = date('Y-m-d', strtotime($start_date));
    }

    /**
     * @return string
     */
    public function getEndDate(): ?string
    {
        return $this->end_date;
    }

    /**
     * @param string $end_date
     */
    public function setEndDate(string $end_date): void
    {
        if (!$this->isDate($end_date)) {
            throw new InvalidArgumentException("Invalid format for end date");
        }

        if (!is_null($this->start_date) && $end_date <= $this->start_date) {
            throw new InvalidArgumentException("End date must be after start date");
        }

        $this->end_date = date('Y-m-d', strtotime($end_date));
    }

    /**
     * @return int
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId(int $user_id): void
    {
        if ($user_id <= 0) {
            throw new InvalidArgumentException("Invalid input for user ID");
        }

        $this->user_id = $user_id;
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
        if (is_null($this->user_id) || is_null($this->api_key) || is_null($this->start_date) || is_null($this->end_date)) {
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
