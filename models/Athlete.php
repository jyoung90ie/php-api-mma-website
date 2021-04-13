<?php

class Athlete
{
    // validation constants
    const NAME_MIN_LENGTH = 5;
    const NAME_MAX_LENGTH = 100;
    const HEIGHT_MIN = 100;
    const HEIGHT_MAX = 250;
    const REACH_MIN = 100;
    const REACH_MAX = 250;

    private ?int $id = null;
    private ?string $name = null;
    private ?float $height = null;
    private ?float $reach = null;
    private ?int $stance_id = null;
    private ?string $dob = null;
    private ?mysqli_result $results = null;

    private mysqli $db;
    private string $table = "Athletes";

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id): ?mysqli_result
    {
        $this->setId($id);

        $query = "SELECT * FROM $this->table WHERE AthleteID=$this->id";
        $result = $this->db->query($query);

        if (!empty($result) && $result->num_rows > 0) {
            $this->results = $result;

            $row = $result->fetch_assoc();

            $this->id = $row['AthleteID'];
            $this->name = $row['AthleteName'];
            $this->height = $row['AthleteHeightInCM'];
            $this->reach = $row['AthleteReachInCM'];
            $this->stance_id = $row['AthleteStanceID'];
            $this->dob = $row['AthleteDOB'];

            // reset cursor back to 0 - this lets any other object access contents of results instance var
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

        $query = "INSERT INTO $this->table (AthleteName, AthleteHeightInCM, AthleteReachInCM, AthleteStanceID, AthleteDOB)
                    VALUES ('$this->name', $this->height, $this->reach, $this->stance_id, '$this->dob');";

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

        $query = "UPDATE $this->table SET AthleteName = '$this->name', 
                    AthleteHeightInCM=$this->height, AthleteReachInCM=$this->reach, 
                    AthleteStanceID=$this->stance_id, AthleteDOB='$this->dob'
                WHERE AthleteID=$this->id";

        $result = $this->db->query($query);

        if (!empty($result) && $result) {
            return true;
        }

        return false;
    }

    public function delete(): bool
    {
        $this->validateIdSet();

        $query = "DELETE FROM $this->table WHERE AthleteID=$this->id";

        $result = $this->db->query($query);

        if (!empty($result) && $result) {
            return true;
        }

        return false;
    }

    // utility functions
    private function validateData(): void
    {
        if (is_null($this->name) || is_null($this->height) || is_null($this->reach) || is_null($this->stance_id)
            || is_null($this->dob)) {
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("Invalid ID");
        }
        $this->id = $id;
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

        $this->name = $this->db->real_escape_string($name);
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
        return $this->stance_id;
    }

    /**
     * @param int|null $stance_id
     */
    public function setStanceId(?int $stance_id): void
    {
        $this->stance_id = intval($stance_id);
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
     * @return mysqli_result
     */
    public function getResults(): ?mysqli_result
    {
        return $this->results;
    }

}
