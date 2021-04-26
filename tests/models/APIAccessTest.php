<?php

namespace models;

include_once '../../autoload.php';
include '../../helpers/config.php';

use helpers\Database;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TypeError;

class APIAccessTest extends TestCase
{
    private $db;

    private $apiAccess;
    private $apiKeyValid;
    private $apiKeyInvalid;
    private $testApiId;

    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();

        $this->apiAccess = new APIAccess($this->db);
        $this->apiKeyValid = "phpUnitTest123";
        $this->apiKeyInvalid = "invalidKey";


        // test data
        $user_id = 3;
        $start_date = date('Y-m-d', strtotime('-1 week'));
        $end_date = date('Y-m-d', strtotime('+4 weeks'));

        // update object vars
        $this->apiAccess->setApiKey($this->apiKeyValid);
        $this->apiAccess->setStartDate($start_date);
        $this->apiAccess->setEndDate($end_date);
        $this->apiAccess->setUserId($user_id);

        // create new record in db
        $this->apiAccess->create();
        $this->testApiId = $this->apiAccess->getApiId();
    }

    // run after each test
    public function tearDown(): void
    {
        $this->apiAccess->delete($this->testApiId);
        $this->db = null;
    }


    public function testVerifyKeyValid()
    {
        $result = $this->apiAccess->verifyKey($this->apiKeyValid);

        $expected = true;

        self::assertTrue(isset($result['ApiKey']));
        self::assertEquals($expected, $this->apiAccess->isVerified());

        // check object variables are set
        self::assertNotEmpty($this->apiAccess->getStartDate());
        self::assertNotEmpty($this->apiAccess->getEndDate());
        self::assertNotEmpty($this->apiAccess->getUserId());
    }

    public function testVerifyKeyInvalid()
    {
        $result = $this->apiAccess->verifyKey($this->apiKeyInvalid);

        // api is invalid, expecting no access
        self::assertFalse($result);
        self::assertFalse($this->apiAccess->isVerified());

        // check object variables are NOT set
        self::assertNull($this->apiAccess->getApiKey());
        self::assertNull($this->apiAccess->getStartDate());
        self::assertNull($this->apiAccess->getEndDate());
        self::assertNull($this->apiAccess->getUserId());
        self::assertNull($this->apiAccess->getApiId());
    }

    public function testUpdateValid()
    {
        // test data
        $new_user_id = 3;
        // today's date minus 1 week
        $new_start_date = date('Y-m-d', strtotime('-2 week'));
        // get today's date and add 4 weeks
        $new_end_date = date('Y-m-d', strtotime('+5 weeks'));

        // updates object with properties for valid entry
        $api = $this->apiAccess->verifyKey($this->apiKeyValid);
        $apiId = $this->apiAccess->getApiId();

        self::assertTrue(isset($api['ApiKey']));
        self::assertTrue($this->apiAccess->isVerified());

        // update object vars
        $this->apiAccess->setUserId($new_user_id);
        $this->apiAccess->setStartDate($new_start_date);
        $this->apiAccess->setEndDate($new_end_date);

        self::assertEquals($new_user_id, $this->apiAccess->getUserId());
        self::assertEquals($new_start_date, $this->apiAccess->getStartDate());
        self::assertEquals($new_end_date, $this->apiAccess->getEndDate());

        // perform update
        $result = $this->apiAccess->update($apiId);
        self::assertTrue($result == 1);
    }

    public function testUpdateInvalidNoVarValues()
    {
        // values will be reset when invalid key is supplied
        $this->apiAccess->verifyKey($this->apiKeyInvalid);
        $apiId = $this->apiAccess->getApiId();

        // id is null as ApiKey was invalid
        self::expectException(TypeError::class);

        $this->apiAccess->update($apiId);
    }

    public function testUpdateInvalidNoObjectId()
    {
        // values will be reset when invalid key is supplied
        $this->apiAccess->verifyKey($this->apiKeyInvalid);
        $apiId = $this->apiAccess->getApiId();

        // set object vars
        $this->apiAccess->setUserId(1);
        $this->apiAccess->setStartDate(date('Y-m-d'));
        $this->apiAccess->setEndDate(date('Y-m-d', strtotime('+1 day')));
        $this->apiAccess->setApiKey($this->apiKeyValid);

        // id is null as ApiKey was invalid
        self::expectException(TypeError::class);

        $this->apiAccess->update($apiId);
    }

    public function testCreateAndDeleteValid()
    {
        // values will be reset when invalid key is supplied
        $this->apiAccess->verifyKey($this->apiKeyInvalid);

        // check data is not set
        self::assertNull($this->apiAccess->getApiId());
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
        $apiId = $this->apiAccess->getApiId();
        // check that query ran successfully
        self::assertTrue($create_query > 0);
        // check the object now has an id
        self::assertNotNull($apiId);

        // delete object
        $delete_query = $this->apiAccess->delete($apiId);
        self::assertTrue($delete_query == 1);
    }

    public function testDeleteInvalidNoObjectId()
    {
        // values will be reset when invalid key is supplied
        $this->apiAccess->verifyKey($this->apiKeyInvalid);
        $apiId = $this->apiAccess->getApiId();

        // id is null as ApiKey was invalid
        self::expectException(TypeError::class);

        $this->apiAccess->delete($apiId);
    }
}
