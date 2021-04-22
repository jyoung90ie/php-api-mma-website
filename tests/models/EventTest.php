<?php

namespace models;

require_once '../../autoload.php';

use helpers\Database;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;


class EventTest extends TestCase
{
    private Event $event;
    private ?PDO $db;

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
        // destroy connection
        $this->db = null;
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->event->getEventId());
        self::assertNull($this->event->getLocation());
        self::assertNull($this->event->getDate());
    }

    public function testCreateAndDeleteValid()
    {
        // set object vars
        $this->event->setLocation($this->locationValid);
        $this->event->setDate($this->dateValid);

        // create new record in db
        $data = null;
        $create_query = $this->event->create($data);
        // check that query ran successfully
        self::assertEquals(1, $create_query);
        // check the object now has an id
        $id = $this->event->getEventId();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->event->delete($id);
        self::assertEquals(1, $delete_query);
    }

    public function testGetOneValid()
    {
        $result = $this->event->getOne($this->idValid);

        self::assertTrue(sizeof($result) > 0);

        self::assertEquals($result['EventID'], $this->event->getEventId());
        self::assertEquals($result['EventLocation'], $this->event->getLocation());
        self::assertEquals($result['EventDate'], $this->event->getDate());
    }

    public function testGetOneInvalid()
    {
        $result = $this->event->getOne($this->idInvalid);
        self::assertFalse($result);
    }

    public function testGetAllAndGetResultsValid()
    {
        $results = $this->event->getAll();

        self::assertEquals($this->event->getResults(), $results);
        self::assertTrue(sizeof($results) > 0);
    }

    public function testUpdate()
    {
        $new_event = new Event($this->db);

        // create object
        $new_event->setLocation($this->locationValid);
        $new_event->setDate($this->dateValid);

        $data = null;
        $new_event->create($data);

        $new_event_id = $new_event->getEventId();

        // use existing object to pero

        $new_location = "newValidLocation";
        $new_date = '2020-11-02';

        // update object vars
        $new_event->setLocation($new_location);
        $new_event->setDate($new_date);

        // perform update
        $update_query = $new_event->update($new_event_id);
        self::assertEquals(1, $update_query);

        // set another object to retrieve data for new_athlete to compare
        $this->event->getOne($new_event_id);

        self::assertEquals($new_event->getEventId(), $this->event->getEventId());
        self::assertEquals($new_event->getLocation(), $this->event->getLocation());
        self::assertEquals($new_event->getDate(), $this->event->getDate());

        // delete from db
        self::assertEquals(1, $new_event->delete($new_event_id));
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
        $this->event->setEventId($this->idValid);
        self::assertEquals($this->idValid, $this->event->getEventId());
    }

    public function testSetIdInvalid()
    {
        $this->event->setEventId($this->idInvalid);
        $expected = -1;
        $result = $this->event->getEventId();

        self::assertEquals($expected, $result);
    }

}
