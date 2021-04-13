<?php


use PHPUnit\Framework\TestCase;

include_once "../config/Database.php";
include_once "../models/ResultType.php";

class ResultTypeTest extends TestCase
{
    private ResultType $result_type;
    private mysqli $db;

    // test data vars
    private int $idValid;
    private string $descriptionValid;
    private int $idInvalid;

    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        $this->result_type = new ResultType($this->db);

        // test data
        $this->idValid = 1;
        $this->descriptionValid = "descriptionValid";

        $this->idInvalid = 0;
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->result_type->getId());
        self::assertNull($this->result_type->getDescription());
    }

    public function testCreateAndDeleteValid()
    {
        // set object vars
        $this->result_type->setDescription($this->descriptionValid);

        // create new record in db
        $create_query = $this->result_type->create();
        // check that query ran successfully
        self::assertTrue($create_query);
        // check the object now has an id
        $id = $this->result_type->getId();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->result_type->delete();
        self::assertTrue($delete_query);
    }

    public function testGetOneValid()
    {
        $result = $this->result_type->getOne($this->idValid);

        self::assertTrue($result->num_rows == 1);

        $data = $result->fetch_assoc();

        self::assertEquals($data['ResultTypeID'], $this->result_type->getId());
        self::assertEquals($data['ResultDescription'], $this->result_type->getDescription());
    }

    public function testGetOneInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid ID");

        $this->result_type->getOne($this->idInvalid);
    }

    public function testGetAllAndGetResultsValid()
    {
        $results = $this->result_type->getAll();

        self::assertEquals($this->result_type->getResults(), $results);
        self::assertTrue($results->num_rows > 0);
    }

    public function testUpdate()
    {
        $new_result_type = new ResultType($this->db);

        // create object
        $new_result_type->setDescription($this->descriptionValid);
        $new_result_type->create();

        $new_event_id = $new_result_type->getId();

        // use existing object
        $new_description = "newResultType";

        // update object vars
        $new_result_type->setDescription($new_description);

        // perform update
        $update_query = $new_result_type->update();
        self::assertTrue($update_query);

        // set another object to retrieve data for new_athlete to compare
        $this->result_type->getOne($new_event_id);

        self::assertEquals($new_result_type->getId(), $this->result_type->getId());
        self::assertEquals($new_result_type->getDescription(), $this->result_type->getDescription());

        // delete from db
        self::assertTrue($new_result_type->delete());
    }

    public function testGetSetDescriptionValid()
    {
        $this->result_type->setDescription($this->descriptionValid);
        self::assertEquals($this->descriptionValid, $this->result_type->getDescription());
    }

    public function testGetSetIdValid()
    {
        $this->result_type->setId($this->idValid);
        self::assertEquals($this->idValid, $this->result_type->getId());
    }

    public function testSetIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->result_type->setId($this->idInvalid);
    }

}
