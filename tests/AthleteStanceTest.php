<?php


use PHPUnit\Framework\TestCase;

include_once "../config/Database.php";
include_once "../models/AthleteStance.php";

class AthleteStanceTest extends TestCase
{
    private AthleteStance $stance;
    private mysqli $db;

    // test data vars
    private int $idValid;
    private string $descriptionValid;
    private int $idInvalid;

    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        $this->stance = new AthleteStance($this->db);

        // test data
        $this->idValid = 1;
        $this->descriptionValid = "descriptionValid";

        $this->idInvalid = 0;
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->stance->getId());
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
        $id = $this->stance->getId();
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

        self::assertEquals($data['AthleteStanceID'], $this->stance->getId());
        self::assertEquals($data['StanceDescription'], $this->stance->getDescription());
    }

    public function testGetOneInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid ID");

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

        $new_event_id = $new_stance->getId();

        // use existing object
        $new_description = "newValidStanceDescription";

        // update object vars
        $new_stance->setDescription($new_description);

        // perform update
        $update_query = $new_stance->update();
        self::assertTrue($update_query);

        // set another object to retrieve data for new_athlete to compare
        $this->stance->getOne($new_event_id);

        self::assertEquals($new_stance->getId(), $this->stance->getId());
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
        $this->stance->setId($this->idValid);
        self::assertEquals($this->idValid, $this->stance->getId());
    }

    public function testSetIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->stance->setId($this->idInvalid);
    }

}
