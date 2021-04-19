<?php

require_once "APIAccess.php";
require_once "Permission.php";

class User
{
    const USERNAME_MIN = 5;
    const USERNAME_MAX = 30;
    const PASSWORD_MIN = 8;
    const PASSWORD_MAX = 25;
    const NAME_MIN = 2;
    const NAME_MAX = 100;

    const TABLE = "Users";
    const ROLES_TABLE = "RolePermissions";

    private ?int $id = null;
    private ?string $username = null;
    private ?string $password = null;
    private ?string $email = null;
    private ?string $first_name = null;
    private ?string $last_name = null;
    private ?string $dob = null;
    private ?int $role_id = null;

    private $results = null;

    private bool $authenticated = false;
    private ?array $permissions = null;

    private PDO $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Returns whether a user exists. If user does exist, instance vars are set.
     *
     */
    public function getByUsername(string $username)
    {
        $query = "SELECT * FROM " . self::TABLE . " WHERE UserName=?";
        try {
            $query = $this->db->prepare($query);
            $query->execute([$username]);


            if ($query->rowCount() == 1) {
                $result = $query->fetch();

                $this->setUsername($username);

                $this->id = $result['UserID'];
                $this->email = $result['UserEmail'];
                $this->password = $result['UserPassword'];
                $this->first_name = $result['UserFirstName'];
                $this->last_name = $result['UserLastName'];
                $this->dob = $result['UserDOB'];
                $this->role_id = $result['RoleID'];

                $this->fetchPermissions();

                return $result;
            }

        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }


    public function getByApiKey(string $api_key): bool
    {
        $query = "SELECT UserID FROM " . APIAccess::TABLE . " WHERE ApiKey=?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$api_key]);

            if ($query->rowCount() == 1) {
                $result = $query->fetch();

                $userId = intval($result['UserID']);

                $query = "SELECT UserName FROM " . self::TABLE . " WHERE UserID=?";
                $query = $this->db->prepare($query);
                $query->execute($userId);

                $result = $query->fetch();

                return $this->getByUsername($result['UserName']);
            }

        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }


    public function checkPassword(string $password): bool
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

    /**
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     *
     */
    private function fetchPermissions(): void
    {
        $query = "SELECT * FROM " . self::ROLES_TABLE . " WHERE RoleID=?";
        try {
            $query = $this->db->prepare($query);
            $query->execute($this->role_id);

            $result = $query->fetch();

            // get and store all permissions in object
            // this saves having to query the database for each permissionID
            $permissions = new Permission($this->db);
            // return all records as an associative and indexed array
            $permissions_all = $permissions->getAll();

            $this->permissions = [];

            while ($permission = $result->fetch_assoc()) {
                // array begins at 0, roleId's begin at 1
                $index = intval($permission['PermissionID']) - 1;
                $area = $permissions_all[$index]['PermissionArea'];
                $type = $permissions_all[$index]['PermissionType'];

                array_push($this->permissions, ['Area' => $area, 'Type' => $type]);
            }

        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * @param string $permission_area
     * @param string $permission_type
     * @return bool
     */
    public function hasPermission(string $permission_area, string $permission_type): bool
    {
        $permission = ['Area' => $permission_area, 'Type' => $permission_type];

        return in_array($permission, $this->permissions);
    }

    /**
     * @return bool
     */
    public function createUser(): bool
    {
        $this->validateData();

        // check if username already exists
        if ($this->getByUsername($this->username)) {
            throw new InvalidArgumentException("Username has already been used");
        }

        if ($this->checkEmail()) {
            throw new InvalidArgumentException("Email has already been used");
        }

        $query = "INSERT INTO " . self::TABLE . " 
                        (UserName, UserEmail, UserPassword, UserFirstName, UserLastName, UserDOB, RoleID)
                    VALUES (?, ?, ?, ?, ?, ?, ?);";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->username, $this->email, $this->password, $this->first_name,
                $this->last_name, $this->dob, $this->role_id]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function update(): bool
    {
        $this->validateData();
        $this->validateIdSet();

        $query = "UPDATE " . self::TABLE . " 
                SET 
                    UserName = ?,
                    UserEmail = ?,
                    UserFirstName = ?,
                    UserLastName = ?,
                    UserDOB = ?,
                    RoleID = ?
                WHERE 
                    UserID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->username, $this->email, $this->first_name, $this->last_name, $this->dob,
                $this->role_id, $this->id]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        $this->validateIdSet();

        $query = "DELETE FROM " . self::TABLE . " WHERE UserID=?";

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
        if (is_null($this->username) || is_null($this->email) || is_null($this->password) || is_null($this->first_name)
            || is_null($this->last_name) || is_null($this->dob) || is_null($this->role_id)) {
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
    public function checkEmail(): bool
    {
        $query = "SELECT UserID FROM " . self::TABLE . " WHERE UserEmail=?";
        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->email]);

            return $query->rowCount();
        } catch (PDOException $exception) {
            die($exception->getMessage());
        }
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
        $this->username = $username;
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

        $this->email = strtolower($email);
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
        $this->first_name = $first_name;
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
        $this->last_name = $last_name;
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
     * @return int|null
     */
    public function getRoleId(): ?int
    {
        return $this->role_id;
    }

    /**
     * @param int|null $role_id
     */
    public function setRoleId(?int $role_id): void
    {
        $this->role_id = $role_id;
        // update permissions
        $this->fetchPermissions();
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }
}