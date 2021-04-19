<?php


class FightResult
{
    private ?int $id = null;
    private ?int $fight_id = null;
    private ?int $result_id = null;
    private ?int $winner_id = null;
    private $results = null;

    private PDO $db;
    private string $table = "FightResults";

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        $this->setId($id);

        $query = "SELECT * FROM $this->table WHERE FightResultID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->id]);

            $result = $query->fetch();

            $this->fight_id = $result['FightID'];
            $this->result_id = $result['ResultTypeID'];
            $this->winner_id = $result['WinnerAthleteID'];

            return $result;
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function getByFight(int $fight_id)
    {
        if (!is_numeric($fight_id)) {
            throw new InvalidArgumentException("Invalid Fight ID");
        }

        $this->setFightId($fight_id);

        $query = "SELECT * FROM $this->table WHERE FightID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->fight_id]);

            $result = $query->fetch();

            $this->id = $result['FightResultID'];
            $this->result_id = $result['ResultTypeID'];
            $this->winner_id = $result['WinnerAthleteID'];

            $this->results = $result;

            return $result;
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function getAll()
    {
        $query = "SELECT * FROM $this->table";

        try {
            $query = $this->db->query($query);
            $result = $query->fetchAll();

            $this->results = $result;

            return $result;
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }


    public function create(): bool
    {
        $this->validateData();

        $query = "INSERT INTO $this->table 
                    (FightID, ResultTypeID, WinnerAthleteID)
                    VALUES (?, ?, ?);";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->fight_id, $this->result_id, $this->winner_id]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function update(): bool
    {
        $this->validateData();
        $this->validateIdSet();

        $query = "UPDATE $this->table 
                    SET 
                        FightID = ?, 
                        ResultTypeID = ?, 
                        WinnerAthleteID = ?
                    WHERE 
                        FightResultID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->fight_id, $this->result_id, $this->winner_id, $this->id]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function delete(): bool
    {
        $this->validateIdSet();

        $query = "DELETE FROM $this->table WHERE FightResultID=$this->id";

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
     * @return
     */
    public function getResults()
    {
        return $this->results;
    }
}