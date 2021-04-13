<?php


class FightAthlete
{
    public int $id;
    public int $fight_id;
    public int $athlete_id;

    public mysqli_result $results;

    private mysqli $db;
    private string $table = "FightAthletes";

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne()
    {
        $query = "SELECT * FROM $this->table WHERE FightAthleteID=$this->id";
        $this->results = $this->db->query($query);

        if ($this->results->num_rows > 0) {
            $row = $this->results->fetch_assoc();

            $this->fight_id = $row['FightID'];
            $this->athlete_id = $row['AthleteID'];
        }

        return $this->results;
    }

    public function getByAthlete()
    {
        $query = "SELECT * FROM $this->table WHERE AthleteID=$this->athlete_id";
        $this->results = $this->db->query($query);

        if ($this->results->num_rows > 0) {
            $row = $this->results->fetch_assoc();

            $this->id = $row['FightAthleteID'];
            $this->fight_id = $row['FightID'];
        }

        return $this->results;
    }

    public function getByFight()
    {
        $query = "SELECT * FROM $this->table WHERE FightID=$this->fight_id";
        $this->results = $this->db->query($query);

        if ($this->results->num_rows > 0) {
            $row = $this->results->fetch_assoc();

            $this->id = $row['FightAthleteID'];
            $this->fight_id = $row['FightID'];
        }

        return $this->results;
    }

    public function getAll()
    {
        $query = "SELECT * FROM $this->table";
        $this->results = $this->db->query($query);

        return $this->results;
    }

    public function create(): bool
    {
        $this->escapeData();

        $query = "INSERT INTO $this->table (FightAthleteID, FightID, AthleteID)
                    VALUES ($this->id, $this->fight_id, $this->athlete_id)";

        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            return true;
        }

        return false;
    }

    public function update(): bool
    {
        $this->escapeData();

        $query = "UPDATE $this->table SET FightID = $this->fight_id, 
                    AthleteID=$this->athlete_id
                WHERE FightAthleteID=$this->id";

        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            return true;
        }

        return false;
    }

    public function delete(): bool
    {
        $query = "DELETE FROM $this->table WHERE FightAthleteID=$this->id";

        $result = $this->db->query($query);

        if ($result) {
            return true;
        }

        return false;

    }

    private function escapeData()
    {
    }
}