<?php

namespace models;

include_once '../../autoload.php';
include_once '../../helpers/config.php';

use helpers\Database;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;


class AthleteStanceTest extends TestCase
{
    private $stance;
    private $db;

    // test data vars
    private $idValid;
    private $descriptionValid;
    private $idInvalid;

    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        $this->stance = new AthleteStance($this->db);

        // test data
        $this->idValid = 1;
        $this->descriptionValid = "descriptionValid";

        $this->idInvalid = 0;
    }

    // run after each test
    public function tearDown(): void
    {
        $this->db = null;
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->stance->getStanceId());
        self::assertNull($this->stance->getDescription());
    }

    public function testCreateAndDeleteValid()
    {
        // set object vars
        $this->stance->setDescription($this->descriptionValid);

        // create new record in db
        $create_query = $this->stance->create();
        // check that query ran successfully
        self::assertTrue($create_query);
        // check the object now has an id
        $id = $this->stance->getStanceId();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->stance->delete();
        self::assertTrue($delete_query);
    }

    public function testGetOneValid()
    {
        $result = $this->stance->getOne($this->idValid);

        self::assertTrue($result->num_rows == 1);

        $data = $result->fetch_assoc();

        self::assertEquals($data['AthleteStanceID'], $this->stance->getStanceId());
        self::assertEquals($data['StanceDescription'], $this->stance->getDescription());
    }

    public function testGetOneInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid Athlete Stance ID");

        $this->stance->getOne($this->idInvalid);
    }

    public function testGetAllAndGetResultsValid()
    {
        $results = $this->stance->getAll();

        self::assertEquals($this->stance->getResults(), $results);
        self::assertTrue($results->num_rows > 0);
    }

    public function testUpdate()
    {
        $new_stance = new AthleteStance($this->db);

        // create object
        $new_stance->setDescription($this->descriptionValid);
        $new_stance->create();

        $new_event_id = $new_stance->getStanceId();

        // use existing object
        $new_description = "newValidStanceDescription";

        // update object vars
        $new_stance->setDescription($new_description);

        // perform update
        $update_query = $new_stance->update();
        self::assertTrue($update_query);

        // set another object to retrieve data for new_athlete to compare
        $this->stance->getOne($new_event_id);

        self::assertEquals($new_stance->getStanceId(), $this->stance->getStanceId());
        self::assertEquals($new_stance->getDescription(), $this->stance->getDescription());

        // delete from db
        self::assertTrue($new_stance->delete());
    }

    public function testGetSetDescriptionValid()
    {
        $this->stance->setDescription($this->descriptionValid);
        self::assertEquals($this->descriptionValid, $this->stance->getDescription());
    }

    public function testGetSetIdValid()
    {
        $this->stance->setStanceId($this->idValid);
        self::assertEquals($this->idValid, $this->stance->getStanceId());
    }

    public function testSetIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->stance->setStanceId($this->idInvalid);
    }

}
