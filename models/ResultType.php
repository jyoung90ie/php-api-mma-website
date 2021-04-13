<?php


class ResultType
{
    public ?int $id = null;
    public ?string $description = null;
    public ?mysqli_result $results;

    private mysqli $db;
    private string $table = "ResultTypes";

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id): ?mysqli_result
    {
        $this->setId($id);

        $query = "SELECT * FROM $this->table WHERE ResultTypeID=$this->id";
        $result = $this->db->query($query);

        if (!empty($result) && $result->num_rows > 0) {
            $this->results = $result;

            $row = $result->fetch_assoc();

            $this->description = $row['ResultDescription'];

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

        $query = "INSERT INTO $this->table (ResultDescription)
                    VALUES ('$this->description');";

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

        $query = "UPDATE $this->table SET ResultDescription = '$this->description'
                WHERE ResultTypeID=$this->id";

        $result = $this->db->query($query);

        if (!empty($result) && $result) {
            return true;
        }

        return false;
    }

    public function delete(): bool
    {
        $this->validateIdSet();

        $query = "DELETE FROM $this->table WHERE ResultTypeID=$this->id";

        $result = $this->db->query($query);

        if (!empty($result) && $result) {
            return true;
        }

        return false;
    }

    // utility functions
    private function validateData(): void
    {
        if (is_null($this->description)) {
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
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $this->db->real_escape_string($description);
    }

    /**
     * @return mysqli_result
     */
    public function getResults(): mysqli_result
    {
        return $this->results;
    }
}