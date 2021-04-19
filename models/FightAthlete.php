<?php

/*





 */

class FightAthlete
{
    private ?int $id = null;
    private ?string $fight_id = null;
    private ?string $athlete_id = null;
    private $results = null;

    private PDO $db;
    private string $table = "FightAthletes";

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        $this->setId($id);

        $query = "SELECT * FROM $this->table WHERE FightAthleteID=?";
        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->id]);

            $result = $query->fetch();

            $this->fight_id = $result['FightID'];
            $this->athlete_id = $result['AthleteID'];

            $this->results = $result;

            return $result;
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function getAllByFight(int $fight_id)
    {
        if (!is_numeric($fight_id)) {
            throw new InvalidArgumentException("Invalid Fight ID");
        }

        $this->setFightId($fight_id);

        $query = "SELECT * FROM $this->table WHERE FightID=?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->fight_id]);

            $result = $query->fetchAll();
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


    public function create(): int
    {
        $this->validateData();

        $query = "INSERT INTO $this->table (FightID, AthleteID) VALUES (?, ?)";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->fight_id, $this->athlete_id]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function update(): int
    {
        $this->validateData();
        $this->validateIdSet();

        $query = "UPDATE $this->table 
                    SET 
                        FightID = ?, 
                        AthleteID = ?
                    WHERE 
                        FightAthleteID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->fight_id, $this->athlete_id, $this->id]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function delete(): bool
    {
        $this->validateIdSet();

        $query = "DELETE FROM $this->table WHERE FightAthleteID=?";

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
        if (is_null($this->fight_id) || is_null($this->athlete_id)) {
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
     * @return string
     */
    public function getFightId(): ?string
    {
        return $this->fight_id;
    }

    /**
     * @param string $fight_id
     */
    public function setFightId(string $fight_id): void
    {
        if ($fight_id <= 0) {
            throw new InvalidArgumentException("Invalid Fight ID");
        }
        $this->fight_id = $fight_id;
    }

    /**
     * @return string
     */
    public function getAthleteId(): ?string
    {
        return $this->athlete_id;
    }

    /**
     * @param string $athlete_id
     */
    public function setAthleteId(string $athlete_id): void
    {
        if ($athlete_id <= 0) {
            throw new InvalidArgumentException("Invalid Athlete ID");
        }
        $this->athlete_id = $athlete_id;
    }

    /**
     * @return
     */
    public function getResults()
    {
        return $this->results;
    }
}