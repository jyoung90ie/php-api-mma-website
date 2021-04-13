<?php


class Fight
{
    // validation constants
    const ROUNDS_MIN = 3;
    const ROUNDS_MAX = 5;

    private ?int $id = null;
    private ?int $event_id = null;
    private ?int $referee_id = null;
    private ?int $title_bout = null;
    private ?int $weight_class_id = null;
    private ?int $rounds = null;
    private ?mysqli_result $results = null;

    private mysqli $db;
    private string $table = "Fights";

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id): ?mysqli_result
    {
        $this->setId($id);

        $query = "SELECT * FROM $this->table WHERE FightID=$this->id";
        $result = $this->db->query($query);

        if (!empty($result) && $result->num_rows > 0) {
            $this->results = $result;

            $row = $result->fetch_assoc();

            $this->id = $row['FightID'];
            $this->event_id = $row['EventID'];
            $this->referee_id = $row['RefereeID'];
            $this->title_bout = $row['TitleBout'];
            $this->weight_class_id = $row['WeightClassID'];
            $this->rounds = $row['NumOfRounds'];

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

        $query = "INSERT INTO $this->table (EventID, RefereeID, TitleBout, WeightClassID, NumOfRounds)
                    VALUES ($this->event_id, $this->referee_id, $this->title_bout, $this->weight_class_id, $this->rounds)";

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

        $query = "UPDATE $this->table SET EventID = $this->event_id,
                    RefereeID=$this->referee_id, TitleBout=$this->title_bout,
                    WeightClassID=$this->weight_class_id, NumOfRounds=$this->rounds
                WHERE FightID=$this->id";

        $result = $this->db->query($query);

        if (!empty($result) && $result) {
            return true;
        }

        return false;
    }

    public function delete(): bool
    {
        $this->validateIdSet();

        $query = "DELETE FROM $this->table WHERE FightID=$this->id";

        $result = $this->db->query($query);

        if (!empty($result) && $result) {
            return true;
        }

        return false;
    }

    // utility functions
    private function validateData(): void
    {
        if (is_null($this->event_id) || is_null($this->referee_id) || is_null($this->title_bout) || is_null($this->weight_class_id)
            || is_null($this->rounds)) {
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
     * @return int|null
     */
    public function getEventId(): ?int
    {
        return $this->event_id;
    }

    /**
     * @param int|null $event_id
     */
    public function setEventID(?int $event_id): void
    {
        if ($event_id <= 0) {
            throw new InvalidArgumentException("Invalid Event ID");
        }

        $this->event_id = $event_id;
    }

    /**
     * @return int|null
     */
    public function getRefereeId(): ?int
    {
        return $this->referee_id;
    }

    /**
     * @param int|null $referee_id
     */
    public function setRefereeId(?int $referee_id): void
    {
        if ($referee_id <= 0) {
            throw new InvalidArgumentException("Invalid Referee ID");
        }

        $this->referee_id = floatval($referee_id);
    }

    /**
     * @return int|null
     */
    public function getTitleBout(): ?int
    {
        return $this->title_bout;
    }

    /**
     * @param bool|null $title_bout
     */
    public function setTitleBout(?bool $title_bout): void
    {
        if ($title_bout) {
            $this->title_bout = 1;
        } else {
            $this->title_bout = 0;
        }
    }

    /**
     * @return int|null
     */
    public function getWeightClassId(): ?int
    {
        return $this->weight_class_id;
    }

    /**
     * @param int|null $weight_class_id
     */
    public function setWeightClassId(?int $weight_class_id): void
    {
        if ($weight_class_id <= 0) {
            throw new InvalidArgumentException("Invalid Weight Class ID");
        }

        $this->weight_class_id = $weight_class_id;
    }

    /**
     * @return int|null
     */
    public function getRounds(): ?int
    {
        return $this->rounds;
    }

    /**
     * @param int|null $rounds
     */
    public function setRounds(?int $rounds): void
    {
        if ($rounds < self::ROUNDS_MIN || $rounds > self::ROUNDS_MAX) {
            throw new InvalidArgumentException("Number of rounds must be between " . self::ROUNDS_MIN . "-" .
                self::ROUNDS_MAX);
        }

        $this->rounds = $rounds;
    }

    /**
     * @return mysqli_result
     */
    public function getResults(): ?mysqli_result
    {
        return $this->results;
    }


}