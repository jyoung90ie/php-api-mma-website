<?php


use PHPUnit\Framework\TestCase;

include_once "../config/Database.php";
include_once "../models/Referee.php";

class RefereeTest extends TestCase
{
    private Referee $referee;
    private mysqli $db;

    // test data vars
    private int $idValid;
    private string $nameValid;
    private int $idInvalid;

    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        $this->referee = new Referee($this->db);

        // test data
        $this->idValid = 1;
        $this->nameValid = "nameValid";

        $this->idInvalid = 0;
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->referee->getId());
        self::assertNull($this->referee->getName());
    }

    public function testCreateAndDeleteValid()
    {
        // set object vars
        $this->referee->setName($this->nameValid);

        // create new record in db
        $create_query = $this->referee->create();
        // check that query ran successfully
        self::assertTrue($create_query);
        // check the object now has an id
        $id = $this->referee->getId();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->referee->delete();
        self::assertTrue($delete_query);
    }

    public function testGetOneValid()
    {
        $result = $this->referee->getOne($this->idValid);

        self::assertTrue($result->num_rows == 1);

        $data = $result->fetch_assoc();

        self::assertEquals($data['RefereeID'], $this->referee->getId());
        self::assertEquals($data['RefereeName'], $this->referee->getName());
    }

    public function testGetOneInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid ID");

        $this->referee->getOne($this->idInvalid);
    }

    public function testGetAllAndGetResultsValid()
    {
        $results = $this->referee->getAll();

        self::assertEquals($this->referee->getResults(), $results);
        self::assertTrue($results->num_rows > 0);
    }

    public function testUpdate()
    {
        $new_referee = new Referee($this->db);

        // create object
        $new_referee->setName($this->nameValid);
        $new_referee->create();

        $new_event_id = $new_referee->getId();

        // use existing object to pero

        $new_name = "newRefereeName";

        // update object vars
        $new_referee->setName($new_name);

        // perform update
        $update_query = $new_referee->update();
        self::assertTrue($update_query);

        // set another object to retrieve data for new_athlete to compare
        $this->referee->getOne($new_event_id);

        self::assertEquals($new_referee->getId(), $this->referee->getId());
        self::assertEquals($new_referee->getName(), $this->referee->getName());

        // delete from db
        self::assertTrue($new_referee->delete());
    }


    public function testGetSetNameValid()
    {
        $this->referee->setName($this->nameValid);
        self::assertEquals($this->nameValid, $this->referee->getName());
    }

    public function testGetSetIdValid()
    {
        $this->referee->setId($this->idValid);
        self::assertEquals($this->idValid, $this->referee->getId());
    }

    public function testSetIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->referee->setId($this->idInvalid);
    }

}
