<?php


class User
{
    const USERNAME_MIN = 5;
    const USERNAME_MAX = 30;
    const PASSWORD_MIN = 8;
    const PASSWORD_MAX = 25;
    const NAME_MIN = 2;
    const NAME_MAX = 100;

    private ?int $id = null;
    private ?string $username = null;
    private ?string $password = null;
    private ?string $email = null;
    private ?string $first_name = null;
    private ?string $last_name = null;
    private ?string $dob = null;
    private ?mysqli_result $results = null;

    private bool $authenticated = false;

    private mysqli $db;
    private string $table = "Users";

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Returns whether a user exists. If user does exist, instance vars are set.
     *
     * @param string $username
     * @return bool true - if the user exists; false - otherwise
     */
    public function get_user(string $username): bool
    {
        $username = $this->db->real_escape_string($username);
        $query = "SELECT * FROM Users WHERE UserName='$username'";
        $result = $this->db->query($query);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            $this->setUsername($username);

            $this->id = $row['UserID'];
            $this->email = $row['UserEmail'];
            $this->password = $row['UserPassword'];
            $this->first_name = $row['UserFirstName'];
            $this->last_name = $row['UserLastName'];
            $this->dob = $row['UserDOB'];

            return true;
        }
        return false;
    }

    public function check_password(string $password): bool
    {
        $this->validateIdSet();

        if (is_null($this->password)) {
            throw new InvalidArgumentException("User password field is empty - try calling get_user(username)");
        }

        if (password_verify($password, $this->password)) {
            $this->authenticated = true;
        } else {
            $this->authenticated = false;
        }

        return $this->authenticated;
    }

    public function create_user(): bool
    {
        $this->validateData();

        // check if username already exists
        if ($this->get_user($this->username)) {
            throw new InvalidArgumentException("Username has already been used");
        }

        if ($this->check_email()) {
            throw new InvalidArgumentException("Email has already been used");
        }

        $query = "INSERT INTO $this->table (UserName, UserEmail, UserPassword, UserFirstName, UserLastName, UserDOB)
                    VALUES ('$this->username', '$this->email', '$this->password', '$this->first_name', 
                            '$this->last_name', '$this->dob');";

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

        $query = "UPDATE $this->table SET UserName = '$this->username', UserEmail = '$this->email',
                 UserFirstName = '$this->first_name', UserLastName = '$this->last_name', UserDOB = '$this->dob'
                WHERE UserID=$this->id";

        $result = $this->db->query($query);

        if (!empty($result) && $result) {
            return true;
        }

        return false;
    }

    public function delete(): bool
    {
        $this->validateIdSet();

        $query = "DELETE FROM $this->table WHERE UserID=$this->id";

        $result = $this->db->query($query);

        if (!empty($result) && $result) {
            return true;
        }

        return false;
    }

    // utility functions
    private function validateData(): void
    {
        if (is_null($this->username) || is_null($this->email) || is_null($this->password) || is_null($this->first_name)
            || is_null($this->last_name) || is_null($this->dob)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    private function validateIdSet(): void
    {
        if (!isset($this->id)) {
            throw new InvalidArgumentException("Object Id has no value");
        }
    }

    /**
     * @return bool true - if the email is already used in the users table; false - otherwise
     */
    public function check_email() {
        $query = "SELECT UserID FROM $this->table WHERE UserEmail='$this->email'";
        $result = $this->db->query($query);

        if ($result->num_rows > 0) {
            return true;
        }

        return false;
    }

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
    private function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        if (strlen($username) < self::USERNAME_MIN || strlen($username) > self::USERNAME_MAX) {
            throw new InvalidArgumentException("Username must be between " . self::USERNAME_MIN . "-" .
                self::USERNAME_MAX . " characters long");
        }
        $this->username = $this->db->real_escape_string($username);
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address");
        }

        $this->email = strtolower($this->db->real_escape_string($email));
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    /**
     * @param string|null $first_name
     */
    public function setFirstName(?string $first_name): void
    {
        if (strlen($first_name) < self::NAME_MIN || strlen($first_name) > self::NAME_MAX) {
            throw new InvalidArgumentException("First name must be between " . self::NAME_MIN . "-" .
                self::NAME_MAX . " characters long");
        }
        $this->first_name = $this->db->real_escape_string($first_name);
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    /**
     * @param string|null $last_name
     */
    public function setLastName(?string $last_name): void
    {
        if (strlen($last_name) < self::NAME_MIN || strlen($last_name) > self::NAME_MAX) {
            throw new InvalidArgumentException("Last name must be between " . self::NAME_MIN . "-" .
                self::NAME_MAX . " characters long");
        }
        $this->last_name = $this->db->real_escape_string($last_name);
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
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }


    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        if (strlen($password) < self::PASSWORD_MIN || strlen($password) > self::PASSWORD_MAX) {
            throw new InvalidArgumentException("Password must be between " . self::PASSWORD_MIN . "-" .
                self::PASSWORD_MAX . " characters long");
        }
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }
}