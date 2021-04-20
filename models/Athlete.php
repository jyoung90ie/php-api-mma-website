<?php

namespace models;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;
use TypeError;

class Athlete
{
    // validation constants
    const NAME_MIN_LENGTH = 5;
    const NAME_MAX_LENGTH = 100;
    const HEIGHT_MIN = 100;
    const HEIGHT_MAX = 250;
    const REACH_MIN = 100;
    const REACH_MAX = 250;
    const PERMISSION_AREA = 'ATHLETES';

    private ?int $athleteId = null;
    private ?string $name = null;
    private ?float $height = null;
    private ?float $reach = null;
    private ?int $stanceId = null;
    private ?string $dob = null;
    private $results = null;

    private PDO $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        $this->setAthleteId($id);

        $query = "SELECT * FROM Athletes WHERE AthleteID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->athleteId]);

            if ($query->rowCount() > 0) {
                $athlete = $query->fetch();

                $this->athleteId = $athlete['AthleteID'];
                $this->name = $athlete['AthleteName'];
                $this->height = $athlete['AthleteHeightInCM'];
                $this->reach = $athlete['AthleteReachInCM'];
                $this->stanceId = $athlete['AthleteStanceID'];
                $this->dob = $athlete['AthleteDOB'];

                $this->results = $athlete;
                
                return $athlete;
            }
            
            return false;
                
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function getAll()
    {
        $query = "SELECT * FROM Athletes";
        try {
            $query = $this->db->query($query);

            $result = $query->fetchAll();
            $this->results = $result;

            return $result;
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function create(array $data): int
    {
        if (!is_null($data)) {
            $this->processData($data);
        }

        $query = "INSERT INTO Athletes 
                    (AthleteName, AthleteHeightInCM, AthleteReachInCM, AthleteStanceID, AthleteDOB)
                    VALUES (?, ?, ?, ?, ?);";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->name, $this->height, $this->reach, $this->stanceId, $this->dob]);

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }

    }

    public function update(int $id, array $data = null): int
    {
        $this->setAthleteId($id);

        if (!is_null($data)) {
            $this->processData($data);
        }
        $this->validateData();

        $query = "UPDATE 
                        Athletes 
                    SET 
                        AthleteName = ?, 
                        AthleteHeightInCM = ?, 
                        AthleteReachInCM = ?, 
                        AthleteStanceID = ?, 
                        AthleteDOB = ?
                WHERE 
                        AthleteID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->name, $this->height, $this->reach, $this->stanceId, $this->dob, $this->athleteId]);

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function delete(int $id): int
    {
        $this->setAthleteId($id);

        $query = "DELETE FROM Athletes WHERE AthleteID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->athleteId]);

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    // utility functions
    private function processData(array $data): void
    {
        try {
            $this->setDob($data['AthleteDOB']);
            $this->setName($data['AthleteName']);
            $this->setStanceId($data['AthleteStanceID']);
            $this->setReach($data['AthleteReachInCM']);
            $this->setHeight($data['AthleteHeightInCM']);
        } catch (Exception | TypeError $exception) {
            exit($exception->getMessage());
        }
    }

    private function validateData(): void
    {
        if (is_null($this->name) || is_null($this->height) || is_null($this->reach) || is_null($this->stanceId)
            || is_null($this->dob)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    // getters and setters
    /**
     * @return int|null
     */
    public function getAthleteId(): ?int
    {
        return $this->athleteId;
    }

    /**
     * @param int|null $athleteId
     */
    public function setAthleteId(?int $athleteId): void
    {
        if ($athleteId <= 0) {
            throw new InvalidArgumentException("Invalid Athlete ID");
        }
        $this->athleteId = intval($athleteId);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        if (strlen($name) < self::NAME_MIN_LENGTH || strlen($name) > self::NAME_MAX_LENGTH) {
            throw new InvalidArgumentException("Name length must be between " . self::NAME_MIN_LENGTH . "-" .
                self::NAME_MAX_LENGTH . " characters");
        }

        $this->name = $name;
    }

    /**
     * @return float|null
     */
    public function getHeight(): ?float
    {
        return $this->height;
    }

    /**
     * @param float|null $height
     */
    public function setHeight(?float $height): void
    {
        if ($height < self::HEIGHT_MIN || $height > self::HEIGHT_MAX) {
            throw new InvalidArgumentException("Height must be between " . self::HEIGHT_MIN . "-" .
                self::HEIGHT_MAX . " cm");
        }
        $this->height = floatval($height);
    }

    /**
     * @return float|null
     */
    public function getReach(): ?float
    {
        return $this->reach;
    }

    /**
     * @param float|null $reach
     */
    public function setReach(?float $reach): void
    {
        if ($reach < self::REACH_MIN || $reach > self::REACH_MAX) {
            throw new InvalidArgumentException("Reach must be between " . self::REACH_MIN . "-" .
                self::REACH_MAX . " cm");
        }
        $this->reach = floatval($reach);
    }

    /**
     * @return int|null
     */
    public function getStanceId(): ?int
    {
        return $this->stanceId;
    }

    /**
     * @param int|null $stanceId
     */
    public function setStanceId(?int $stanceId): void
    {
        $this->stanceId = intval($stanceId);
    }

    /**
     * @return string|null
     */
    public function getDob(): ?string
    {
        return $this->dob;
    }

    /**
     * @param string|null $dob
     */
    public function setDob(?string $dob): void
    {
        if (!strtotime($dob)) {
            throw new InvalidArgumentException("Invalid date for DOB");
        }

        $this->dob = date("Y-m-d", strtotime($dob));
    }

    /**
     * @return null
     */
    public function getResults()
    {
        return $this->results;
    }

}
