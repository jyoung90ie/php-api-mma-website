<?php

namespace models;

use InvalidArgumentException;
use PDO;
use PDOException;

class FightAthlete
{
    private  $fightAthleteId = null;
    private $fightId = null;
    private $athleteId = null;
    private $results = null;

    private $db;
    private $table = "FightAthletes";

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        $this->setFightAthleteId($id);

        $query = "SELECT * FROM FightAthletes WHERE FightAthleteID=?";
        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->fightAthleteId]);

            if ($query->rowCount() > 0) {
                $result = $query->fetch();

                $this->fightId = $result['FightID'];
                $this->athleteId = $result['AthleteID'];

                $this->results = $result;

                return $result;
            }
            return false;
        } catch (PDOException | \Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function getAllByFight(int $fightId)
    {
        $this->setFightId($fightId);

        $query = "SELECT * FROM FightAthletes WHERE FightID=?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->fightId]);

            $result = $query->fetchAll();
            $this->results = $result;

            return $result;
        } catch (PDOException | \Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function getAll()
    {
        $query = "SELECT * FROM FightAthletes";
        try {
            $query = $this->db->query($query);
            
            $result = $query->fetchAll();
            $this->results = $result;

            return $result;
        } catch (PDOException | \Exception $exception) {
            die($exception->getMessage());
        }
    }


    public function create(): int
    {
        $this->validateData();

        $query = "INSERT INTO FightAthletes (FightID, AthleteID) VALUES (?, ?)";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->fightId, $this->athleteId]);

            return $query->rowCount();
        } catch (PDOException | \Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function update(): int
    {
        $this->validateData();
        $this->validateIdSet();

        $query = "UPDATE 
                        FightAthletes
                    SET 
                        FightID = ?, 
                        AthleteID = ?
                    WHERE 
                        FightAthleteID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->fightId, $this->athleteId, $this->fightAthleteId]);

            return $query->rowCount();
        } catch (PDOException | \Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function delete(): bool
    {
        $this->validateIdSet();

        $query = "DELETE FROM FightAthletes WHERE FightAthleteID=?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->fightAthleteId]);

            return $query->rowCount();
        } catch (PDOException | \Exception $exception) {
            die($exception->getMessage());
        }
    }

    // utility functions
    private function validateData(): void
    {
        if (is_null($this->fightId) || is_null($this->athleteId)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    private function validateIdSet(): void
    {
        if (!isset($this->fightAthleteId)) {
            throw new InvalidArgumentException("Object Id has no value");
        }
    }

    // getters and setters

    /**
     * @return int
     */
    public function getFightAthleteId(): ?int
    {
        return $this->fightAthleteId;
    }

    /**
     * @param int $fightAthleteId
     */
    public function setFightAthleteId(int $fightAthleteId): void
    {
        if ($fightAthleteId <= 0) {
            throw new InvalidArgumentException("Invalid ID");
        }
        $this->fightAthleteId = $fightAthleteId;
    }

    /**
     * @return string
     */
    public function getFightId(): ?string
    {
        return $this->fightId;
    }

    /**
     * @param string $fightId
     */
    public function setFightId(string $fightId): void
    {
        if ($fightId <= 0) {
            throw new InvalidArgumentException("Invalid Fight ID");
        }
        $this->fightId = $fightId;
    }

    /**
     * @return string
     */
    public function getAthleteId(): ?string
    {
        return $this->athleteId;
    }

    /**
     * @param string $athleteId
     */
    public function setAthleteId(string $athleteId): void
    {
        if ($athleteId <= 0) {
            throw new InvalidArgumentException("Invalid Athlete ID");
        }
        $this->athleteId = $athleteId;
    }

    /**
     * @return
     */
    public function getResults()
    {
        return $this->results;
    }
}