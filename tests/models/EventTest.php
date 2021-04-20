<?php


use PHPUnit\Framework\TestCase;

include_once "../helpers/Database.php";
include_once "../models/Event.php";

class EventTest extends TestCase
{
    private Event $event;
    private mysqli $db;

    // test data vars
    private int $idValid;
    private string $locationValid;
    private string $dateValid;
    private int $idInvalid;
    private string $dateInvalid;
    private string $dateInvalidBelowMin;

    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        $this->event = new Event($this->db);

        // test data
        $this->idValid = 1;
        $this->locationValid = "nameValid";
        $this->dateValid = date('Y-m-d');

        $this->idInvalid = 0;
        $this->dateInvalid = '31/02/1990';
        $this->dateInvalidBelowMin = '1993-11-11'; // see event::DATE_MIN
    }

    // run after each test
    public function tearDown(): void
    {
        $this->db->close();
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->event->getId());
        self::assertNull($this->event->getLocation());
        self::assertNull($this->event->getDate());
    }

    public function testCreateAndDeleteValid()
    {
        // set object vars
        $this->event->setLocation($this->locationValid);
        $this->event->setDate($this->dateValid);

        // create new record in db
        $create_query = $this->event->create();
        // check that query ran successfully
        self::assertTrue($create_query);
        // check the object now has an id
        $id = $this->event->getId();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->event->delete();
        self::assertTrue($delete_query);
    }

    public function testGetOneValid()
    {
        $result = $this->event->getOne($this->idValid);

        self::assertTrue($result->num_rows == 1);

        $data = $result->fetch_assoc();

        self::assertEquals($data['EventID'], $this->event->getId());
        self::assertEquals($data['EventLocation'], $this->event->getLocation());
        self::assertEquals($data['EventDate'], $this->event->getDate());
    }

    public function testGetOneInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid Event ID");

        $this->event->getOne($this->idInvalid);
    }

    public function testGetAllAndGetResultsValid()
    {
        $results = $this->event->getAll();

        self::assertEquals($this->event->getResults(), $results);
        self::assertTrue($results->num_rows > 0);
    }

    public function testUpdate()
    {
        $new_event = new Event($this->db);

        // create object
        $new_event->setLocation($this->locationValid);
        $new_event->setDate($this->dateValid);

        $new_event->create();

        $new_event_id = $new_event->getId();

        // use existing object to pero

        $new_location = "newValidLocation";
        $new_date = '2020-11-02';

        // update object vars
        $new_event->setLocation($new_location);
        $new_event->setDate($new_date);

        // perform update
        $update_query = $new_event->update();
        self::assertTrue($update_query);

        // set another object to retrieve data for new_athlete to compare
        $this->event->getOne($new_event_id);

        self::assertEquals($new_event->getId(), $this->event->getId());
        self::assertEquals($new_event->getLocation(), $this->event->getLocation());
        self::assertEquals($new_event->getDate(), $this->event->getDate());

        // delete from db
        self::assertTrue($new_event->delete());
    }

    public function testGetSetDate()
    {
        $this->event->setDate($this->dateValid);
        self::assertEquals($this->dateValid, $this->event->getDate());
    }

    public function testGetSetDateInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->event->setDate($this->dateInvalid);
    }

    public function testSetDateBelowMinInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->event->setDate($this->dateInvalidBelowMin);
    }

    public function testGetSetLocationValid()
    {
        $this->event->setLocation($this->locationValid);
        self::assertEquals($this->locationValid, $this->event->getLocation());
    }

    public function testGetSetIdValid()
    {
        $this->event->setId($this->idValid);
        self::assertEquals($this->idValid, $this->event->getId());
    }

    public function testSetIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->event->setId($this->idInvalid);
    }

}
