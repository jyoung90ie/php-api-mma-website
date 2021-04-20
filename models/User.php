<?php

namespace models;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;
use TypeError;

class User
{
    const USERNAME_MIN = 5;
    const USERNAME_MAX = 30;
    const PASSWORD_MIN = 8;
    const PASSWORD_MAX = 25;
    const NAME_MIN = 2;
    const NAME_MAX = 100;
    const PERMISSION_AREA = 'USERS';


    private ?int $userId = null;
    private ?string $username = null;
    private ?string $password = null;
    private ?string $email = null;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $dob = null;
    private ?int $roleId = null;

    private $results = null;

    private bool $authenticated = false;
    private ?array $permissions = null;

    private PDO $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $userId)
    {
        $this->setUserId($userId);
        try {
            $query = "SELECT 
                            UserID,
                            UserName,
                            UserEmail,
                            UserFirstName,
                            UserLastName,
                            UserDOB,
                            RoleID
                        FROM 
                            Users 
                        WHERE 
                            UserID=?";
            $query = $this->db->prepare($query);
            $query->execute([$this->userId]);

            if ($query->rowCount() == 1) {
                $result = $query->fetch();

                $this->username = $result['UserName'];
                $this->email = $result['UserEmail'];
                $this->firstName = $result['UserFirstName'];
                $this->lastName = $result['UserLastName'];
                $this->dob = $result['UserDOB'];
                $this->roleId = $result['RoleID'];

                return $result;
            }

            return false;
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function getAll(): array
    {
        $query = "SELECT 
                    UserID,
                    UserName,
                    UserEmail,
                    UserFirstName,
                    UserLastName,
                    UserDOB,
                    RoleID
                FROM 
                    Users";
        try {
            $query = $this->db->query($query);

            $result = $query->fetchAll();
            $this->results = $result;

            return $result;
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        if (!is_null($data)) {
            $this->processData($data);
        }

        $this->validateData();

        // this is required because it is not checked as part of validateData
        if (is_null($this->password)) {
            throw new InvalidArgumentException('Password must have a value');
        }

        // check if username already exists
        if ($this->checkUsernameExists($this->username)) {
            throw new InvalidArgumentException("Username has already been used");
        }

        if ($this->checkEmail()) {
            throw new InvalidArgumentException("Email has already been used");
        }

        $query = "INSERT INTO Users 
                        (UserName, UserEmail, UserPassword, UserFirstName, UserLastName, UserDOB, RoleID)
                    VALUES (?, ?, ?, ?, ?, ?, ?);";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->username, $this->email, $this->password, $this->firstName,
                $this->lastName, $this->dob, $this->roleId]);

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * @param int $id
     * @param array|null $data
     * @return int
     */
    public function update(int $id, array $data = null): int
    {
        $this->setUserId($id);

        $this->setUsername($data['UserName']);
        $this->setEmail($data['UserEmail']);
        $this->setFirstName($data['UserFirstName']);
        $this->setLastName($data['UserLastName']);
        $this->setDob($data['UserDOB']);
        $this->setRoleId($data['RoleID']);

        $this->validateData();

        $query = "UPDATE Users 
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
            $query->execute([$this->username, $this->email, $this->firstName, $this->lastName, $this->dob,
                $this->roleId, $this->userId]);

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        $this->setUserId($id);

        $query = "DELETE FROM Users WHERE UserID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->userId]);

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    // utility functions

    private function processData(array $data): void
    {
        try {
            $this->setUsername($data['UserName']);
            $this->setEmail($data['UserEmail']);
            $this->setPassword($data['UserPassword']);
            $this->setFirstName($data['UserFirstName']);
            $this->setLastName($data['UserLastName']);
            $this->setDob($data['UserDOB']);
            $this->setRoleId($data['RoleID']);
        } catch (Exception | TypeError $exception) {
            exit($exception->getMessage());
        }
    }


    /**
     * Validates a user's password against the value in the database. This works by comparing hashes.
     *
     * @param string $password
     * @return bool true if passwords match
     */
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
     * Retrieves user permissions from the database based on the user's roleID. The result is stored in
     * the instance variable, permissions, in the format [Area=MODULE_NAME, Type=READ/CREATE/UPDATE/DELETE]
     */
    public function fetchPermissions(): void
    {
        $query = "SELECT * FROM RolePermissions WHERE RoleID=?";
        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->roleId]);

            if ($query->rowCount() > 0) {

                $permissions = $this->db->query("SELECT * FROM Permissions");
                $listPermissionsAll = $permissions->fetchAll(PDO::FETCH_CLASS);

                // store user's permissions in instance array
                $this->permissions = [];

                while ($permission = $query->fetch()) {
                    // array begins at 0, roleId's begin at 1
                    foreach ($listPermissionsAll as $singlePermission) {
                        if ($singlePermission->PermissionID == $permission['PermissionID']) {
                            $area = $singlePermission->PermissionArea;
                            $type = $singlePermission->PermissionType;

                            array_push($this->permissions, ['Area' => $area, 'Type' => $type]);
                            break;
                        }
                    }
                }
            }


        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * Determines whether a username already exists.
     *
     * @param string $username
     * @return bool
     */
    public function checkUsernameExists(string $username): bool
    {
        $query = "SELECT * FROM Users WHERE UserName=?";
        try {
            $query = $this->db->prepare($query);
            $query->execute([$username]);


            if ($query->rowCount() > 0) {
                return true;
            }

            return false;
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function getByApiKey(string $apiKey)
    {
        $query = "SELECT UserID FROM ApiAccess WHERE ApiKey=?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$apiKey]);

            if ($query->rowCount() == 1) {
                $result = $query->fetch();
                $userId = intval($result['UserID']);

                return $this->getOne($userId);
            }
            return false;
        } catch (PDOException | Exception $exception) {
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

    // utility functions

    private function validateData(): void
    {
        if (is_null($this->username) || is_null($this->email) || is_null($this->firstName)
            || is_null($this->lastName) || is_null($this->dob) || is_null($this->roleId)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    private function validateIdSet(): void
    {
        if (!isset($this->userId)) {
            throw new InvalidArgumentException("Object Id has no value");
        }
    }

    /**
     * @return bool true - if the email is already used in the users table; false - otherwise
     */
    public function checkEmail(): bool
    {
        $query = "SELECT UserID FROM Users WHERE UserEmail=?";
        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->email]);

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int|null $id
     */
    private function setUserId(?int $id): void
    {
        $this->userId = $id;
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
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     */
    public function setFirstName(?string $firstName): void
    {
        if (strlen($firstName) < self::NAME_MIN || strlen($firstName) > self::NAME_MAX) {
            throw new InvalidArgumentException("First name must be between " . self::NAME_MIN . "-" .
                self::NAME_MAX . " characters long");
        }
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     */
    public function setLastName(?string $lastName): void
    {
        if (strlen($lastName) < self::NAME_MIN || strlen($lastName) > self::NAME_MAX) {
            throw new InvalidArgumentException("Last name must be between " . self::NAME_MIN . "-" .
                self::NAME_MAX . " characters long");
        }
        $this->lastName = $lastName;
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
        return $this->roleId;
    }

    /**
     * @param int|null $roleId
     */
    public function setRoleId(?int $roleId): void
    {
        $this->roleId = $roleId;
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }
}