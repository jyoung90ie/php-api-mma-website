<?php


namespace models;

include_once '../../autoload.php';
include_once '../../helpers/config.php';

use helpers\Database;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;


class UserTest extends TestCase
{
    private $user;
    private $persistentUser;
    private $db;

    // test data vars
    private $idValid;
    private $userNameValid;
    private $userEmailValid;
    private $userPasswordValid;
    private $persistentPasswordValid;
    private $userFirstNameValid;
    private $userLastNameValid;
    private $userDobValid;
    private $permissionsValid;

    private $userRoleIdValid;
    private $userPermissionIdValid;
    private $idInvalid;
    private $userNameInvalid;
    private $userEmailInvalid;
    private $userPasswordInvalid;
    private $userFirstNameInvalid;
    private $userLastNameInvalid;
    private $userDobInvalid;
    private $userRoleIdInvalid;


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
        $this->persistentUser = new User($this->db);
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

        $this->persistentUser->setUsername("persistentUser1");
        $this->persistentUser->setEmail("persistentUser@test.com");
        $this->persistentUser->setPassword($this->persistentPasswordValid);
        $this->persistentUser->setFirstName("firstName");
        $this->persistentUser->setLastName("lastName");
        $this->persistentUser->setDob('1960-12-30');
        $this->persistentUser->setRoleId($this->userRoleIdValid);
        $this->persistentUser->setPermissions($this->permissionsValid);

        // create object in database
        $this->persistentUser->create(null);
    }

    public function tearDown(): void
    {
        // delete user from db
        $userId = $this->persistentUser->getUserId();
        $this->persistentUser->delete($userId);
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->user->getUserId());
        self::assertNull($this->user->getUsername());
        self::assertNull($this->user->getEmail());
        self::assertNull($this->user->getPassword());
        self::assertNull($this->user->getFirstName());
        self::assertNull($this->user->getLastName());
        self::assertNull($this->user->getDob());
        self::assertNull($this->user->getRoleId());
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
        $create_query = $this->user->create(null);
        // check that query ran successfully
        self::assertTrue($create_query > 0);
        // check the object now has an id
        $id = $this->user->getUserId();

        self::assertNotNull($id);

        // delete object
        $delete_query = $this->user->delete($this->user->getUserId());
        self::assertTrue($delete_query > 0);
    }

    public function testGetUser()
    {
        $this->user->getUserByUsername($this->persistentUser->getUsername());

        self::assertEquals($this->persistentUser->getUserId(), $this->user->getUserId());
        self::assertEquals($this->persistentUser->getUsername(), $this->user->getUsername());
        self::assertEquals($this->persistentUser->getEmail(), $this->user->getEmail());
        self::assertEquals($this->persistentUser->getFirstName(), $this->user->getFirstName());
        self::assertEquals($this->persistentUser->getLastName(), $this->user->getLastName());
        self::assertEquals($this->persistentUser->getDob(), $this->user->getDob());
        self::assertEquals($this->persistentUser->getRoleId(), $this->user->getRoleId());
    }

    public function testCheckPassword()
    {
        // get data from one test object
        $this->user->getUserByUsername($this->persistentUser->getUsername());

        // test 1 - using wrong password

        self::assertFalse($this->user->verifyLoginCredentials($this->persistentUser->getUsername(), $this->userPasswordInvalid));

        // test 2 - using the correct password
        self::assertTrue(sizeof($this->user->verifyLoginCredentials($this->persistentUser->getUsername(), $this->persistentPasswordValid)) > 0);

        // test 3 - repeating test one to check that authenticated is reset to false
        self::assertFalse($this->user->verifyLoginCredentials($this->persistentUser->getUsername(), $this->userPasswordInvalid));
    }

    public function testCheckEmail()
    {
        $this->user->setEmail($this->persistentUser->getEmail());
        self::assertTrue($this->user->checkEmail());

        $this->user->setEmail($this->userEmailValid);
        self::assertFalse($this->user->checkEmail());
    }


    public function testUpdate()
    {
        // make sure user does not exist
        self::assertFalse($this->user->getUserByUsername($this->userNameValid));

        // update persistent user
        $this->persistentUser->setUsername($this->userNameValid);
        $this->persistentUser->setEmail($this->userEmailValid);
        $this->persistentUser->update($this->persistentUser->getUserId());

        // check that update took place
        self::assertTrue(sizeof($this->user->getUserByUsername($this->userNameValid)) > 0);

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
        self::assertNull($this->user->getUserId());
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
        $rolePermissions = new RolePermission($this->db);
        $rolePermissions->setRoleId($role->getRoleId());


        $rolePermissions->setPermissions($permissions);
        $rolePermissions->create();

        // update user permissions
        $this->user->getUserByUsername($this->persistentUser->getUsername());
        $this->user->setRoleId($role->getRoleId());
        $this->user->fetchPermissions();

        $permission = new Permission($this->db);

        // check user has permission
        foreach ($permissions as $permissionId) {
            $permission->getOne($permissionId);
            self::assertTrue($this->user->hasPermission($permission->getArea(), $permission->getType()));
        }

        self::assertEquals(sizeof($permissions), sizeof($this->user->getPermissions()));

        $rolePermissions->delete();
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
        $api->setUserId($this->persistentUser->getUserId());
        $api->create();
        $apiId = $api->getApiId();


        // get the user_id by supplying the apiKey
        $valid_api_key = $this->user->getUserByApiKey($api_key);

        self::assertTrue($valid_api_key > 0);
        self::assertEquals($this->user->getUserId(), $this->persistentUser->getUserId());

        // remove api from db and check it happened
        self::assertTrue($api->delete($apiId) > 0);
    }

    public function testFetchPermissions()
    {

        // create new role for testing
        $role = new Role($this->db);
        $role->setDescription("Test Role");
        $role->create();

        // create the permissions for the new role
        $role_permissions = new RolePermission($this->db);
        $role_permissions->setRoleId($role->getRoleId());
        $role_permissions->setPermissions([1, 2, 3, 4, 5, 9, 10]);
        $role_permissions->create();

        // set the role and update db
        $this->persistentUser->setRoleId($role->getRoleId());
        $this->persistentUser->update($this->persistentUser->getUserId());

        // update object permissions
        $this->persistentUser->fetchPermissions();

        $expected_permissions = [
            ['Area' => 'FIGHTS', 'Type' => 'CREATE'],
            ['Area' => 'FIGHTS', 'Type' => 'READ'],
            ['Area' => 'FIGHTS', 'Type' => 'UPDATE'],
            ['Area' => 'FIGHTS', 'Type' => 'DELETE'],
            ['Area' => 'ATHLETES', 'Type' => 'CREATE'],
            ['Area' => 'EVENTS', 'Type' => 'CREATE'],
            ['Area' => 'EVENTS', 'Type' => 'READ'],
        ];


        self::assertEquals($expected_permissions, $this->persistentUser->getPermissions());

        // delete role and permissions
        $role_permissions->delete();
        // change role id so that the role can be deleted
        $this->persistentUser->setRoleId($this->userRoleIdValid);
        $this->persistentUser->update($this->persistentUser->getUserId());

        $role->delete();
    }

}
