<?php


class FightResult
{
    public ?int $id = null;
    public ?int $fight_id = null;
    public ?int $result_id = null;
    public ?int $winner_id = null;
    public ?mysqli_result $results = null;

    private mysqli $db;
    private string $table = "FightResults";

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id): ?mysqli_result
    {
        $this->setId($id);

        $query = "SELECT * FROM $this->table WHERE FightResultID=$this->id";
        $result = $this->db->query($query);

        if (!empty($result) && $result->num_rows > 0) {
            $this->results = $result;

            $row = $result->fetch_assoc();

            $this->fight_id = $row['FightID'];
            $this->result_id = $row['ResultTypeID'];
            $this->winner_id = $row['WinnerAthleteID'];

            // reset cursor back to 0
            $this->results->data_seek(0);
        } else {
            $this->results = null;
        }

        return $this->results;
    }

    public function getAll(): ?mysqli_result
    {
        $query = "SELECT * FROM $this->table";
        $results = $this->db->query($query);

        if (!empty($results) && $results->num_rows > 0) {
            $this->results = $results;
        } else {
            $this->results = null;
        }

        return $this->results;
    }


    public function create(): bool
    {
        $this->validateData();

        $query = "INSERT INTO $this->table (FightID, ResultTypeID, WinnerAthleteID)
                    VALUES ($this->fight_id, $this->result_id, $this->winner_id);";

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

        $query = "UPDATE $this->table SET FightID=$this->fight_id, 
                    ResultTypeID=$this->result_id, WinnerAthleteID=$this->winner_id
                WHERE FightResultID=$this->id";

        $result = $this->db->query($query);

        if (!empty($result) && $result) {
            return true;
        }

        return false;
    }

    public function delete(): bool
    {
        $this->validateIdSet();

        $query = "DELETE FROM $this->table WHERE FightResultID=$this->id";

        $result = $this->db->query($query);

        if (!empty($result) && $result) {
            return true;
        }

        return false;
    }

    // utility functions
    private function validateData(): void
    {
        if (is_null($this->fight_id) || is_null($this->result_id) || is_null($this->winner_id)) {
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
     * @return int|null
     */
    public function getFightId(): ?int
    {
        return $this->fight_id;
    }

    /**
     * @param int $fight_id
     */
    public function setFightId(int $fight_id): void
    {
        if ($fight_id <= 0) {
            throw new InvalidArgumentException("Invalid Fight ID");
        }

        $this->fight_id = $fight_id;
    }

    /**
     * @return int|null
     */
    public function getResultId(): ?int
    {
        return $this->result_id;
    }

    /**
     * @param int $result_id
     */
    public function setResultId(int $result_id): void
    {
        if ($result_id <= 0) {
            throw new InvalidArgumentException("Invalid Result Type ID");
        }

        $this->result_id = $result_id;
    }

    /**
     * @return int|null
     */
    public function getWinnerId(): ?int
    {
        return $this->winner_id;
    }

    /**
     * @param int $winner_id
     */
    public function setWinnerId(int $winner_id): void
    {
        if ($winner_id <= 0) {
            throw new InvalidArgumentException("Invalid winner Athlete ID");
        }

        $this->winner_id = $winner_id;
    }

    /**
     * @return mysqli_result
     */
    public function getResults(): ?mysqli_result
    {
        return $this->results;
    }
}