<?php


use PHPUnit\Framework\TestCase;

include_once "../helpers/Database.php";
include_once "../models/User.php";
include_once "../models/APIAccess.php";
include_once "../models/Role.php";
include_once "../models/RolePermission.php";
include_once "../models/Permission.php";

class UserTest extends TestCase
{
    private User $user;
    private User $persistent_user;
    private mysqli $db;

    // test data vars
    private int $idValid;
    private string $userNameValid;
    private string $userEmailValid;
    private string $userPasswordValid;
    private string $persistentPasswordValid;
    private string $userFirstNameValid;
    private string $userLastNameValid;
    private string $userDobValid;
    private array $permissionsValid;

    private int $userRoleIdValid;
    private int $userPermissionIdValid;
    private int $idInvalid;
    private string $userNameInvalid;
    private string $userEmailInvalid;
    private string $userPasswordInvalid;
    private string $userFirstNameInvalid;
    private string $userLastNameInvalid;
    private string $userDobInvalid;
    private int $userRoleIdInvalid;
    private int $userPermissionIdInvalid;


    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        $this->user = new User($this->db);


        // test data
        $this->idValid = 1;
        $this->userNameValid = 'validUser1';
        $this->userEmailValid = 'validUser1@email.com';
        $this->userPasswordValid = 'validPassword123';
        $this->userFirstNameValid = 'validFirstName';
        $this->userLastNameValid = 'validLastName';
        $this->userDobValid = date('Y-m-d');
        $this->userRoleIdValid = 4;
        $this->userPermissionIdValid = 1;

        $this->idInvalid = 0;
        $this->userNameInvalid = 'no';
        $this->userEmailInvalid = 'invalidEmail.com';
        $this->userPasswordInvalid = 'invalid';
        $this->userFirstNameInvalid = '';
        $this->userLastNameInvalid = 'l';
        $this->userDobInvalid = '12-34-2020';
        $this->userRoleIdInvalid = 99;

        // setup a test user that can be used in each test
        $this->persistent_user = new User($this->db);
        $this->persistentPasswordValid = 'persistentUserPassword';

        $areaUsers = 'USERS';
        $areaFights = 'FIGHTS';

        // create new role
        $this->permissionsValid = [
            ['Area' => $areaUsers, 'Type' => 'CREATE'],
            ['Area' => $areaUsers, 'Type' => 'READ'],

            ['Area' => $areaFights, 'Type' => 'CREATE'],
            ['Area' => $areaFights, 'Type' => 'READ'],
            ['Area' => $areaFights, 'Type' => 'UPDATE'],
        ];

        $this->persistent_user->setUsername("persistentUser1");
        $this->persistent_user->setEmail("persistentUser@test.com");
        $this->persistent_user->setPassword($this->persistentPasswordValid);
        $this->persistent_user->setFirstName("firstName");
        $this->persistent_user->setLastName("lastName");
        $this->persistent_user->setDob('1960-12-30');
        $this->persistent_user->setRoleId($this->userRoleIdValid);
        $this->persistent_user->setPermissions($this->permissionsValid);

        // create object in database
        $this->persistent_user->createUser();
    }

    public function tearDown(): void
    {
        // delete user from db
        $this->persistent_user->delete();
        $this->db->close();
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->user->getId());
        self::assertNull($this->user->getUsername());
        self::assertNull($this->user->getEmail());
        self::assertNull($this->user->getPassword());
        self::assertNull($this->user->getFirstName());
        self::assertNull($this->user->getLastName());
        self::assertNull($this->user->getDob());
        self::assertNull($this->user->getRoleId());
        self::assertFalse($this->user->isAuthenticated());
    }

    public function testCreateAndDeleteValid()
    {
        // set object vars
        $this->user->setUsername($this->userNameValid);
        $this->user->setEmail($this->userEmailValid);
        $this->user->setPassword($this->userPasswordValid);
        $this->user->setFirstName($this->userFirstNameValid);
        $this->user->setLastName($this->userLastNameValid);
        $this->user->setDob($this->userDobValid);
        $this->user->setRoleId($this->userRoleIdValid);

        // create new record in db
        $create_query = $this->user->createUser();
        // check that query ran successfully
        self::assertTrue($create_query);
        // check the object now has an id
        $id = $this->user->getId();

        self::assertNotNull($id);
        self::assertFalse($this->user->isAuthenticated());

        // delete object
        $delete_query = $this->user->delete();
        self::assertTrue($delete_query);
    }

    public function testGetUser()
    {
        $this->user->getByUsername($this->persistent_user->getUsername());

        self::assertEquals($this->persistent_user->getId(), $this->user->getId());
        self::assertEquals($this->persistent_user->getUsername(), $this->user->getUsername());
        self::assertEquals($this->persistent_user->getEmail(), $this->user->getEmail());
        self::assertEquals($this->persistent_user->getPassword(), $this->user->getPassword());
        self::assertEquals($this->persistent_user->getFirstName(), $this->user->getFirstName());
        self::assertEquals($this->persistent_user->getLastName(), $this->user->getLastName());
        self::assertEquals($this->persistent_user->getDob(), $this->user->getDob());
        self::assertEquals($this->persistent_user->getRoleId(), $this->user->getRoleId());
    }

    public function testCheckPassword()
    {
        // get data from one test object
        $this->user->getByUsername($this->persistent_user->getUsername());

        // test 1 - using wrong password
        self::assertFalse($this->user->checkPassword($this->userPasswordInvalid));
        self::assertFalse($this->user->isAuthenticated());

        // test 2 - using the correct password
        self::assertTrue($this->user->checkPassword($this->persistentPasswordValid));
        self::assertTrue($this->user->isAuthenticated());

        // test 3 - repeating test one to check that authenticated is reset to false
        self::assertFalse($this->user->checkPassword($this->userPasswordInvalid));
        self::assertFalse($this->user->isAuthenticated());
    }

    public function testCheckEmail()
    {
        $this->user->setEmail($this->persistent_user->getEmail());
        self::assertTrue($this->user->checkEmail());

        $this->user->setEmail($this->userEmailValid);
        self::assertFalse($this->user->checkEmail());
    }


    public function testUpdate()
    {
        // make sure user does not exist
        self::assertFalse($this->user->getByUsername($this->userNameValid));

        // update persistent user
        $this->persistent_user->setUsername($this->userNameValid);
        $this->persistent_user->setEmail($this->userEmailValid);
        $this->persistent_user->update();

        // check that update took place
        self::assertTrue($this->user->getByUsername($this->userNameValid));

        // setEmail changes email to lowercase
        self::assertEqualsIgnoringCase($this->userNameValid, $this->user->getUsername());
        self::assertEqualsIgnoringCase($this->userEmailValid, $this->user->getEmail());
    }


    public function testSetFirstNameValid()
    {
        $this->user->setFirstName($this->userFirstNameValid);
        self::assertEquals($this->userFirstNameValid, $this->user->getFirstName());
    }

    public function testSetFirstNameInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->user->setFirstName($this->userFirstNameInvalid);
    }

    public function testSetLastNameValid()
    {
        $this->user->setLastName($this->userLastNameValid);
        self::assertEquals($this->userLastNameValid, $this->user->getLastName());
    }

    public function testSetLastNameInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->user->setLastName($this->userLastNameInvalid);
    }

    public function testGetId()
    {
        self::assertNull($this->user->getId());
    }

    public function testSetUsername()
    {
        $this->user->setUsername($this->userNameValid);
        self::assertEquals($this->userNameValid, $this->user->getUsername());
    }

    public function testSetUsernameInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->user->setUsername($this->userNameInvalid);
    }

    public function testSetPasswordValid()
    {
        $this->user->setPassword($this->userPasswordValid);
        // test by verifying that password results in same hash as that in user object
        self::assertTrue(password_verify($this->userPasswordValid, $this->user->getPassword()));
    }

    public function testSetPasswordInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->user->setPassword($this->userPasswordInvalid);
    }

    public function testSetDobValid()
    {
        $this->user->setDob($this->userDobValid);
        self::assertEquals($this->userDobValid, $this->user->getDob());
    }

    public function testSetDobInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->user->setDob($this->userDobInvalid);
    }

    public function testSetEmailValid()
    {
        $this->user->setEmail($this->userEmailValid);
        // setEmail changes email address to be lower case
        self::assertEqualsIgnoringCase($this->userEmailValid, $this->user->getEmail());
    }

    public function testSetEmailInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->user->setEmail($this->userEmailInvalid);
    }

    public function testHasPermission()
    {
        // list of permission ids
        $permissions = [1, 2, 5, 6, 9, 10];

        // create new role for testing
        $role = new Role($this->db);
        $role->setDescription("Test Role");
        $role->create();

        // create the permissions for the new role
        $role_permissions = new RolePermission($this->db);
        $role_permissions->setRoleId($role->getId());


        $role_permissions->setPermissions($permissions);
        $role_permissions->create();

        // update user permissions
        $this->user->getByUsername($this->persistent_user->getUsername());
        $this->user->setRoleId($role->getId());

        $permission = new Permission($this->db);

        // check user has permission
        foreach ($permissions as $permission_id) {
            $permission->getOne($permission_id);
            self::assertTrue($this->user->hasPermission($permission->getArea(), $permission->getType()));
        }

        self::assertEquals(sizeof($permissions), sizeof($this->user->getPermissions()));

        $role_permissions->delete();
        $role->delete();
    }


    public function testGetUserByAPI()
    {

        $api_key = 'A1b2C3_';

        // create new api
        $api = new APIAccess($this->db);
        $api->setApiKey($api_key);
        $api->setStartDate('2021-01-01');
        $api->setEndDate('2022-12-01');
        $api->setUserId($this->persistent_user->getId());
        $api->create();

        // get the user_id by supplying the apiKey
        $valid_api_key = $this->user->getByApiKey($api_key);

        self::assertTrue($valid_api_key);
        self::assertEquals($this->user->getId(), $this->persistent_user->getId());

        // remove api from db and check it happened
        self::assertTrue($api->delete());
    }

    public function testFetchPermissions()
    {

        // create new role for testing
        $role = new Role($this->db);
        $role->setDescription("Test Role");
        $role->create();

        // create the permissions for the new role
        $role_permissions = new RolePermission($this->db);
        $role_permissions->setRoleId($role->getId());
        $role_permissions->setPermissions([1, 2, 3, 4, 5, 9, 10]);
        $role_permissions->create();

        // set the role and update db
        $this->persistent_user->setRoleId($role->getId());
        $this->persistent_user->update();

        $expected_permissions = [
            ['Area' => 'FIGHTS', 'Type' => 'CREATE'],
            ['Area' => 'FIGHTS', 'Type' => 'READ'],
            ['Area' => 'FIGHTS', 'Type' => 'UPDATE'],
            ['Area' => 'FIGHTS', 'Type' => 'DELETE'],
            ['Area' => 'ATHLETES', 'Type' => 'CREATE'],
            ['Area' => 'EVENTS', 'Type' => 'CREATE'],
            ['Area' => 'EVENTS', 'Type' => 'READ'],
        ];


        self::assertEquals($expected_permissions, $this->persistent_user->getPermissions());

        // delete role and permissions
        $role_permissions->delete();
        // change role id so that the role can be deleted
        $this->persistent_user->setRoleId($this->userRoleIdValid);
        $this->persistent_user->update();

        $role->delete();
    }

}
