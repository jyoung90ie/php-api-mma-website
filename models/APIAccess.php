<?php


class APIAccess
{
    private ?int $id = null;
    private ?string $api_key = null;
    private ?string $start_date = null;
    private ?string $end_date = null;
    private ?int $user_id = null;
    private bool $verified = false;

    private mysqli $db;
    private string $table = "APIAccess";

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function verifyKey($api_key): bool
    {
        $this->api_key = $this->db->real_escape_string($api_key);

        $now = date('Y-m-d');

        $query = "SELECT * FROM $this->table WHERE ApiKey='$this->api_key'"
            . " AND (StartDate <= '$now' OR StartDate IS NULL) AND (EndDate >= '$now' OR EndDate IS NULL)";
        $result = $this->db->query($query);


        if (!empty($result) && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $start_date = $row['StartDate'];
            $end_date = $row['EndDate'];
            $user_id = $row['UserID'];

            $this->id = $row['ID'];
            $this->start_date = (is_null($start_date)) ? "" : $start_date;
            $this->end_date = (is_null($end_date)) ? "" : $end_date;
            $this->user_id = (is_null($user_id)) ? -1 : $user_id;
            $this->verified = true;

            return true;
        }

        return false;
    }

    public function create(): bool
    {
        $this->validateData();

        $query = "INSERT INTO $this->table(ApiKey, StartDate, EndDate, UserID)
                    VALUES('$this->api_key', '$this->start_date', '$this->end_date', $this->user_id);";

        $result = $this->db->query($query);

        if (!empty($result) && $result) {
            $this->id = $this->db->insert_id;

            return true;
        }

        return false;
    }

    public function update(): bool
    {
        $this->validateData();
        $this->validateIdSet();

        $query = "UPDATE $this->table SET ApiKey = '$this->api_key', 
                    StartDate = '$this->start_date', EndDate = '$this->end_date', UserID = '$this->user_id'
                WHERE ID = $this->id";

        $result = $this->db->query($query);

        if (!empty($result) && $result) {
            return true;
        }

        return false;
    }

    public function delete(): bool
    {
        $this->validateIdSet();

        $query = "DELETE FROM $this->table WHERE ID = $this->id";

        $result = $this->db->query($query);

        if (!empty($result) && $result) {
            return true;
        }

        return false;
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
        $this->api_key = $this->db->real_escape_string($api_key);
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

    private function isDate(string $date): bool {
        if (strtotime($date)) {
            return true;
        }

        return false;
    }
}
