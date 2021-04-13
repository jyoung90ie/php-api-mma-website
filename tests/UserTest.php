<?php


use PHPUnit\Framework\TestCase;

include_once "../config/Database.php";
include_once "../models/User.php";

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
    private int $idInvalid;
    private string $userNameInvalid;
    private string $userEmailInvalid;
    private string $userPasswordInvalid;
    private string $userFirstNameInvalid;
    private string $userLastNameInvalid;
    private string $userDobInvalid;


    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        $this->user = new User($this->db);

        // setup a test user that can be used in each test
        $this->persistent_user = new User($this->db);
        $this->persistentPasswordValid = 'persistentUserPassword';

        $this->persistent_user->setUsername("persistentUser1");
        $this->persistent_user->setEmail("persistentUser@test.com");
        $this->persistent_user->setPassword($this->persistentPasswordValid);
        $this->persistent_user->setFirstName("firstName");
        $this->persistent_user->setLastName("lastName");
        $this->persistent_user->setDob('1960-12-30');

        // create object in database
        $this->persistent_user->create_user();


        // test data
        $this->idValid = 1;
        $this->userNameValid = 'validUser1';
        $this->userEmailValid = 'validUser1@email.com';
        $this->userPasswordValid = 'validPassword123';
        $this->userFirstNameValid = 'validFirstName';
        $this->userLastNameValid = 'validLastName';
        $this->userDobValid = date('Y-m-d');;

        $this->idInvalid = 0;
        $this->userNameInvalid = 'no';
        $this->userEmailInvalid = 'invalidEmail.com';
        $this->userPasswordInvalid = 'invalid';
        $this->userFirstNameInvalid = '';
        $this->userLastNameInvalid = 'l';
        $this->userDobInvalid = '12-34-2020';
    }

    public function tearDown(): void
    {
        // delete user from db
        $this->persistent_user->delete();
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

        // create new record in db
        $create_query = $this->user->create_user();
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
        $this->user->get_user($this->persistent_user->getUsername());

        self::assertEquals($this->persistent_user->getId(), $this->user->getId());
        self::assertEquals($this->persistent_user->getUsername(), $this->user->getUsername());
        self::assertEquals($this->persistent_user->getEmail(), $this->user->getEmail());
        self::assertEquals($this->persistent_user->getPassword(), $this->user->getPassword());
        self::assertEquals($this->persistent_user->getFirstName(), $this->user->getFirstName());
        self::assertEquals($this->persistent_user->getLastName(), $this->user->getLastName());
        self::assertEquals($this->persistent_user->getDob(), $this->user->getDob());
    }

    public function testCheckPassword()
    {
        // get data from one test object
        $this->user->get_user($this->persistent_user->getUsername());

        // test 1 - using wrong password
        self::assertFalse($this->user->check_password($this->userPasswordInvalid));
        self::assertFalse($this->user->isAuthenticated());

        // test 2 - using the correct password
        self::assertTrue($this->user->check_password($this->persistentPasswordValid));
        self::assertTrue($this->user->isAuthenticated());

        // test 3 - repeating test one to check that authenticated is reset to false
        self::assertFalse($this->user->check_password($this->userPasswordInvalid));
        self::assertFalse($this->user->isAuthenticated());
    }

    public function testCheckEmail()
    {
        $this->user->setEmail($this->persistent_user->getEmail());
        self::assertTrue($this->user->check_email());

        $this->user->setEmail($this->userEmailValid);
        self::assertFalse($this->user->check_email());
    }


    public function testUpdate()
    {
        // make sure user does not exist
        self::assertFalse($this->user->get_user($this->userNameValid));

        // update persistent user
        $this->persistent_user->setUsername($this->userNameValid);
        $this->persistent_user->setEmail($this->userEmailValid);
        $this->persistent_user->update();

        // check that update took place
        self::assertTrue($this->user->get_user($this->userNameValid));

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
}
