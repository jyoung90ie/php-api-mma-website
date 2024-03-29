<?php

namespace models;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;

class User
{
    const USERNAME_MIN = 5;
    const USERNAME_MAX = 30;
    const PASSWORD_MIN = 8;
    const PASSWORD_MAX = 25;
    const NAME_MIN = 2;
    const NAME_MAX = 100;
    const PERMISSION_AREA = 'USERS';


    private $userId = null;
    private $username = null;
    private $password = null;
    private $email = null;
    private $firstName = null;
    private $lastName = null;
    private $dob = null;
    private $roleId = null;

    private $results = null;

    private $permissions = null;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Retrieves all fields for a specified user from the database.
     *
     * @param int $userId - to retrieve records for
     * @return false|mixed - user data if userId exists, otherwise, return false
     */
    public function getOne(int $userId)
    {
        $this->setUserId($userId);
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
    }

    /**
     * Retrieves all user records from the database and returns them.
     *
     * @return array containing all user records
     */
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
        $query = $this->db->query($query);

        $result = $query->fetchAll();
        $this->results = $result;

        return $result;
    }

    /**
     * Retrieves the total records in the database - used for pagination, to calculate pages.
     *
     * @return int total number of records.
     */
    public function getTotal(): int
    {
        $query = $this->db->query("SELECT * FROM Users");
        return $query->rowCount();
    }

    /**
     * Create a new user account in the database.
     * @param array|null $data - form data with all required database fields.
     * @return int number of rows impacted by the delete query: 1 if successful, 0 if not.
     */
    public function create(array $data): int
    {
        $this->processData($data);
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

        $query = $this->db->prepare($query);
        $query->execute([$this->username, $this->email, $this->password, $this->firstName,
            $this->lastName, $this->dob, $this->roleId]);

        $rowCount = $query->rowCount();

        if ($rowCount > 0) {
            $this->userId = $this->db->lastInsertId();
        }

        return $rowCount;
    }

    /**
     * Update user data in the database, except the password.
     * @param int $userId - for the account to be updated
     * @param array|null $data - form data with specified fields
     * @return int number of rows impacted by the delete query: 1 if successful, 0 if not.
     */
    public function update(int $userId, array $data = null): int
    {
        $this->setUserId($userId ?? 0);
        $this->setUsername($data['UserName'] ?? '');
        $this->setEmail($data['UserEmail'] ?? '');
        $this->setFirstName($data['UserFirstName'] ?? '');
        $this->setLastName($data['UserLastName'] ?? '');
        $this->setDob($data['UserDOB'] ?? '');
        $this->setRoleId($data['RoleID'] ?? 0);

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

        $query = $this->db->prepare($query);
        $query->execute([$this->username, $this->email, $this->firstName, $this->lastName, $this->dob,
            $this->roleId, $this->userId]);

        return $query->rowCount();
    }

    /**
     * Deletes the user account with the specified id.
     *
     * @param int $userId - for the user to be deleted
     * @return int number of rows impacted by the delete query: 1 if successful, 0 if not.
     */
    public function delete(int $userId): int
    {
        $this->setUserId($userId);

        $query = "DELETE FROM Users WHERE UserID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->userId]);

        return $query->rowCount();
    }

    // utility functions

    /**
     * Accepts an array of data, containing specified elements. This will process the data array by calling object
     * setters for each value. This ensures data is validated prior to use.
     *
     * @param array $data
     */
    private function processData(array $data): void
    {
        $this->setUsername($data['UserName'] ?? '');
        $this->setEmail($data['UserEmail'] ?? '');
        $this->setPassword($data['UserPassword'] ?? '');
        $this->setFirstName($data['UserFirstName'] ?? '');
        $this->setLastName($data['UserLastName'] ?? '');
        $this->setDob($data['UserDOB'] ?? '');
        $this->setRoleId($data['RoleID'] ?? 0);
    }


    /**
     * Validates username and password against database records. This works by comparing hashes.
     *
     * @param string $username account username
     * @param string $password account password
     * @return false|mixed returns user data if credentials match, otherwise false
     */
    public function verifyLoginCredentials(string $username, string $password)
    {

        $query = "SELECT * FROM Users WHERE UserName=?;";
        $query = $this->db->prepare($query);
        $query->execute([$username]);

        if ($query->rowCount() > 0) {
            $result = $query->fetch();

            if (password_verify($password, $result['UserPassword'])) {
                // get user permissions and return as result
                $this->roleId = $result['RoleID'];
                $this->fetchPermissions();

                $result['Permissions'] = $this->permissions;

                return $result;
            }
        }
        return false;
    }


    /**
     * Retrieves user permissions from the database based on the user's roleID. The result is stored in
     * the instance variable, permissions, in the format [Area=MODULE_NAME, Type=READ/CREATE/UPDATE/DELETE]
     *
     * Permissions are stored in instance var, $this->permissions
     */
    public function fetchPermissions(): void
    {
        $query = "SELECT * FROM RolePermissions WHERE RoleID=?";
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
        $query = $this->db->prepare($query);
        $query->execute([$username]);

        return ($query->rowCount() > 0);
    }

    /**
     * Gets user data from the database.
     *
     * @param string $username - the username that will be used to retrieve data.
     * @return false|mixed - user data for the specified username, if it exists, otherwise returns false.
     */
    public function getUserByUserName(string $username)
    {
        $query = "SELECT UserID FROM Users WHERE UserName=?";
        $query = $this->db->prepare($query);
        $query->execute([$username]);


        if ($query->rowCount() > 0) {
            $result = $query->fetch();
            return $this->getOne($result['UserID']);
        }

        return false;
    }

    /**
     * Returns the user associated with the ApiKey.
     *
     * @param string $apiKey
     * @return false|mixed - database results if user exists, otherwise, return false
     */
    public function getUserByApiKey(string $apiKey)
    {
        $query = "SELECT UserID FROM ApiAccess WHERE ApiKey=?";

        $query = $this->db->prepare($query);
        $query->execute([$apiKey]);

        if ($query->rowCount() == 1) {
            $result = $query->fetch();
            $userId = intval($result['UserID']);

            return $this->getOne($userId);
        }
        return false;
    }

    /**
     * Determines whether a user has sufficient permission to access the specific functionality via API/front-end.
     *
     * @param string $permissionModule - the module being accessed, e.g. fight/event/user/etc.
     * @param string $permissionType - the type of access required, e.g. create/read/update/delete
     * @return bool
     */
    public function hasPermission(string $permissionModule, string $permissionType): bool
    {
        $permission = ['Area' => $permissionModule, 'Type' => $permissionType];

        return in_array($permission, $this->permissions);
    }

    // utility functions

    /**
     * Checks the specified instance vars to ensure they have all been populated. An exception will be thrown if any of
     * the vars have not been populated.
     *
     * Note: this does not check if password has been populated. This will need to be assessed in specific functions.
     */
    private function validateData(): void
    {
        if (is_null($this->username) || is_null($this->email) || is_null($this->firstName)
            || is_null($this->lastName) || is_null($this->dob) || is_null($this->roleId)) {
            throw new InvalidArgumentException("User operation could not be executed due to invalid fields");
        }
    }

    /**
     * Searches the database to see if the email has already been used. This is to prevent multiple accounts sharing the
     * same email address.
     *
     * @return bool true - if the email is already used in the users table; false - otherwise
     */
    public function checkEmail(): bool
    {
        $query = "SELECT UserID FROM Users WHERE UserEmail=?";
        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->email]);

            return ($query->rowCount() > 0);
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
        if (!is_numeric($id) || $id <= 0) {
            throw new InvalidArgumentException('Invalid value for UserID');
        }
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
            throw new InvalidArgumentException("Invalid value for UserName. Username must be between " . self::USERNAME_MIN . "-" .
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
            throw new InvalidArgumentException("Invalid value for UserEmail");
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
            throw new InvalidArgumentException("Invalid value for UserFirstName. First name must be between " . self::NAME_MIN . "-" .
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
            throw new InvalidArgumentException("Invalid value for UserLastName. Last name must be between " . self::NAME_MIN . "-" .
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
            throw new InvalidArgumentException("Invalid value for UserDOB");
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
            throw new InvalidArgumentException("Invalid value for UserPassword. Password must be between " . self::PASSWORD_MIN . "-" .
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
        // check roleId is valid
        $role = new Role($this->db);
        if ($role->getOne($roleId) === false) {
            throw new InvalidArgumentException('Invalid value for RoleID');
        }
        $this->roleId = $roleId;
    }

}