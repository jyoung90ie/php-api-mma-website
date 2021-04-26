<?php

namespace models;

include_once '../../autoload.php';
include_once '../../helpers/config.php';

use \helpers\Database;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;


class AthleteTest extends TestCase
{
    private $athlete;
    private $db;

    // test data vars
    private $idValid;
    private $nameValid;
    private $dobValid;
    private $heightValid;
    private $reachValid;
    private $stanceIdValid;
    private $idInvalid;
    private $nameInvalid;
    private $dobInvalid;
    private $heightInvalid;
    private $reachInvalid;
    private $stanceIdInvalid;

    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        $this->athlete = new Athlete($this->db);

        // test data
        $this->idValid = 1;
        $this->nameValid = "nameValid";
        $this->heightValid = 100;
        $this->reachValid = 100;
        $this->stanceIdValid = 1;
        $this->dobValid = date('Y-m-d');

        $this->idInvalid = 0;
        $this->nameInvalid = 'na';
        $this->reachInvalid = 99;
        $this->heightInvalid = 99;
        $this->stanceIdInvalid = 0;
        $this->dobInvalid = '31/02/1990';
    }

    // run after each test
    public function tearDown(): void
    {
        // close connection
        $this->db = null;
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->athlete->getAthleteID());
        self::assertNull($this->athlete->getName());
        self::assertNull($this->athlete->getHeight());
        self::assertNull($this->athlete->getReach());
        self::assertNull($this->athlete->getStanceId());
        self::assertNull($this->athlete->getDob());
        self::assertNull($this->athlete->getResults());
    }

    public function testCreateAndDeleteValid()
    {
        // set object vars
        $this->athlete->setName($this->nameValid);
        $this->athlete->setHeight($this->heightValid);
        $this->athlete->setReach($this->reachValid);
        $this->athlete->setStanceId($this->stanceIdValid);
        $this->athlete->setDob($this->dobValid);

        // as the data points have been set above, don't need to pass in a data array
        $data = null;

        // create new record in db
        $create_query = $this->athlete->create($data);
        // check that query ran successfully
        self::assertTrue($create_query == 1);
        // check the object now has an id
        $id = $this->athlete->getAthleteID();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->athlete->delete($id);
        self::assertTrue($delete_query == 1);
    }

    public function testGetOneValid()
    {
        $result = $this->athlete->getOne($this->idValid);

        self::assertTrue(sizeof($result) > 0 );

        self::assertEquals($result['AthleteID'], $this->athlete->getAthleteID());
        self::assertEquals($result['AthleteName'], $this->athlete->getName());
        self::assertEquals($result['AthleteHeightInCM'], $this->athlete->getHeight());
        self::assertEquals($result['AthleteReachInCM'], $this->athlete->getReach());
        self::assertEquals($result['AthleteStanceID'], $this->athlete->getStanceId());
        self::assertEquals($result['AthleteDOB'], $this->athlete->getDob());
    }

    public function testGetOneInvalid()
    {
        $result = $this->athlete->getOne($this->idInvalid);

        self::assertFalse($result);
    }

    public function testGetAllAndGetResultsValid()
    {
        $results = $this->athlete->getAll();

        self::assertEquals($this->athlete->getResults(), $results);
        self::assertTrue(sizeof($results) > 0);
    }

    public function testUpdate()
    {

        $new_athlete = new Athlete($this->db);

        // create object
        $new_athlete->setName($this->nameValid);
        $new_athlete->setHeight($this->heightValid);
        $new_athlete->setReach($this->reachValid);
        $new_athlete->setStanceId($this->stanceIdValid);
        $new_athlete->setDob($this->dobValid);

        $data = null;

        $new_athlete->create($data);

        $new_athlete_id = $new_athlete->getAthleteID();

        // use existing object to pero

        $new_name = "newValidName";
        $new_height = 123;
        $new_reach = 249;
        $new_stance_id = 3;
        $new_dob = \DateTime::createFromFormat('d-m-Y', '14-12-1990')->format('Y-m-d');

        // update object vars
        $new_athlete->setName($new_name);
        $new_athlete->setHeight($new_height);
        $new_athlete->setReach($new_reach);
        $new_athlete->setStanceId($new_stance_id);
        $new_athlete->setDob($new_dob);

        // perform update
        $data = null;
        $update_query = $new_athlete->update($new_athlete_id, $data);
        self::assertTrue($update_query == 1);

        // set another object to retrieve data for new_athlete to compare
        $this->athlete->getOne($new_athlete_id);

        self::assertEquals($new_athlete->getAthleteID(), $this->athlete->getAthleteID());
        self::assertEquals($new_athlete->getName(), $this->athlete->getName());
        self::assertEquals($new_athlete->getHeight(), $this->athlete->getHeight());
        self::assertEquals($new_athlete->getReach(), $this->athlete->getReach());
        self::assertEquals($new_athlete->getStanceId(), $this->athlete->getStanceId());
        self::assertEquals($new_athlete->getDob(), $this->athlete->getDob());

        // delete from db
        self::assertTrue($new_athlete->delete($new_athlete_id) == 1);
    }

    public function testSetGetHeightValid()
    {
        $this->athlete->setHeight($this->heightValid);
        self::assertEquals($this->heightValid, $this->athlete->getHeight());
    }

    public function testSetGetHeightInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->athlete->setHeight($this->heightInvalid);
    }

    public function testSetGetDob()
    {
        $this->athlete->setDob($this->dobValid);
        self::assertEquals($this->dobValid, $this->athlete->getDob());
    }

    public function testSetGetDobInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->athlete->setDob($this->dobInvalid);
    }

    public function testSetGetStanceIdValid()
    {
        $this->athlete->setStanceId($this->stanceIdValid);
        self::assertEquals($this->stanceIdValid, $this->athlete->getStanceId());
    }

    public function testSetGetStanceIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->athlete->setDob($this->stanceIdInvalid);
    }

    public function testSetGetReachValid()
    {
        $this->athlete->setReach($this->reachValid);
        self::assertEquals($this->reachValid, $this->athlete->getReach());
    }

    public function testSetGetReachInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->athlete->setDob($this->reachInvalid);
    }

    public function testSetNameValid()
    {
        $this->athlete->setName($this->nameValid);
        self::assertEquals($this->nameValid, $this->athlete->getName());
    }

    public function testSetNameInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->athlete->setName($this->nameInvalid);
    }

    public function testGetIdValid()
    {
        $this->athlete->setAthleteId($this->idValid);
        self::assertEquals($this->idValid, $this->athlete->getAthleteID());
    }

    public function testGetIdInvalid()
    {
        $this->athlete->setAthleteId($this->idInvalid);

        $result = $this->athlete->getAthleteId();
        $expected = -1;

        self::assertEquals($expected, $result);
    }
}
