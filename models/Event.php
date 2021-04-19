<?php


class Event
{
    const DATE_MIN = '1993-11-12'; // date of first ever event

    private ?int $id = null;
    private ?string $location = null;
    private ?string $date = null;
    private $results = null;

    private PDO $db;
    private string $table = "Events";

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        // performs validation checks before setting
        $this->setId($id);

        $query = "SELECT * FROM $this->table WHERE EventID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->id]);

            $result = $query->fetch();

            $this->location = $result['EventLocation'];
            $this->date = $result['EventDate'];

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

        $query = "INSERT INTO $this->table (EventLocation, EventDate) VALUES (?, ?);";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->location, $this->date]);

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
                        EventLocation = ?, 
                        EventDate = ?
                    WHERE 
                        EventID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->location, $this->date, $this->id]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    public function delete(): int
    {
        $this->validateIdSet();

        $query = "DELETE FROM $this->table WHERE EventID = ?";

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
        if (is_null($this->location) || is_null($this->date)) {
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
            throw new InvalidArgumentException("Invalid Event ID");
        }
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getDate(): ?string
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate(string $date): void
    {
        if (!strtotime($date)) {
            throw new InvalidArgumentException("Invalid date for DOB");
        }

        if (strtotime(self::DATE_MIN) > strtotime($date)) {
            throw new InvalidArgumentException("Date must be on or after " . self::DATE_MIN);
        }

        $this->date = date("Y-m-d", strtotime($date));
    }

    /**
     * @return
     */
    public function getResults()
    {
        return $this->results;
    }
}