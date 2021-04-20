<?php


use PHPUnit\Framework\TestCase;

// sets working directory to test folder - enables tests to be run all together
chdir(__DIR__);

include_once "../helpers/Database.php";
include_once "../models/APIAccess.php";

class APIAccessTest extends TestCase
{
    private mysqli $db;

    private APIAccess $apiAccess;
    private string $apiKeyValid;
    private string $apiKeyInvalid;

    public function setUp(): void {
        $this->db = (new Database())->getConnection();

        $this->apiAccess = new APIAccess($this->db);
        $this->apiKeyValid = "test123";
        $this->apiKeyInvalid = "invalidKey";
    }

    // run after each test
    public function tearDown(): void
    {
        $this->db->close();
    }


    public function testVerifyKeyValid()
    {
        $result = $this->apiAccess->verifyKey($this->apiKeyValid);

        $expected = true;

        self::assertEquals($expected, $result);
        self::assertEquals($expected, $this->apiAccess->isVerified());

        // check object variables are set
        self::assertNotEmpty($this->apiAccess->getStartDate());
        self::assertNotEmpty($this->apiAccess->getEndDate());
        self::assertNotEmpty($this->apiAccess->getUserId());
    }

    public function testVerifyKeyInvalid()
    {
        $result = $this->apiAccess->verifyKey($this->apiKeyInvalid);

        $expected = false;

        // api is invalid, expecting no access
        self::assertEquals($expected, $result);
        self::assertEquals($expected, $this->apiAccess->isVerified());

        // check that the api_key was updated to match
        self::assertEquals($this->apiKeyInvalid, $this->apiAccess->getApiKey());

        // check object variables are NOT set
        self::assertNull($this->apiAccess->getStartDate());
        self::assertNull($this->apiAccess->getEndDate());
        self::assertNull($this->apiAccess->getUserId());
        self::assertNull($this->apiAccess->getId());
    }

    public function testUpdateValid()
    {
        // test data
        $new_user_id = 3;
        // today's date minus 1 week
        $new_start_date = date('Y-m-d', strtotime('-1 week'));
        // get today's date and add 4 weeks
        $new_end_date = date('Y-m-d', strtotime('+4 weeks'));

        // updates object with properties for valid entry
        $api = $this->apiAccess->verifyKey($this->apiKeyValid);

        self::assertTrue($api);
        self::assertTrue($this->apiAccess->isVerified());

        // update object vars
        $this->apiAccess->setUserId($new_user_id);
        $this->apiAccess->setStartDate($new_start_date);
        $this->apiAccess->setEndDate($new_end_date);

        self::assertEquals($new_user_id, $this->apiAccess->getUserId());
        self::assertEquals($new_start_date, $this->apiAccess->getStartDate());
        self::assertEquals($new_end_date, $this->apiAccess->getEndDate());

        // perform update
        $result = $this->apiAccess->update();
        self::assertTrue($result);
    }

    public function testUpdateInvalidNoVarValues()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage("All object variables must have a value");

        $this->apiAccess->update();
    }

    public function testUpdateInvalidNoObjectId()
    {
        // set object vars
        $this->apiAccess->setUserId(1);
        $this->apiAccess->setStartDate(date('Y-m-d'));
        $this->apiAccess->setEndDate(date('Y-m-d', strtotime('+1 day')));
        $this->apiAccess->setApiKey($this->apiKeyValid);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage("Object Id has no value");

        $this->apiAccess->update();
    }

    public function testCreateAndDeleteValid()
    {
        // check data is not set
        self::assertNull($this->apiAccess->getId());
        self::assertNull($this->apiAccess->getUserId());
        self::assertNull($this->apiAccess->getStartDate());
        self::assertNull($this->apiAccess->getEndDate());
        self::assertNull($this->apiAccess->getApiKey());
        self::assertFalse($this->apiAccess->isVerified());

        // test data
        $user_id = 3;
        $start_date = date('Y-m-d', strtotime('-1 week'));
        $end_date = date('Y-m-d', strtotime('+4 weeks'));
        $api_key = "newAPIKey";

        // update object vars
        $this->apiAccess->setApiKey($api_key);
        $this->apiAccess->setStartDate($start_date);
        $this->apiAccess->setEndDate($end_date);
        $this->apiAccess->setUserId($user_id);

        // create new record in db
        $create_query = $this->apiAccess->create();
        // check that query ran successfully
        self::assertTrue($create_query);
        // check the object now has an id
        $id = $this->apiAccess->getId();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->apiAccess->delete();
        self::assertTrue($delete_query);
    }

    public function testDeleteInvalidNoObjectId()
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage("Object Id has no value");

        $this->apiAccess->delete();
    }
}
