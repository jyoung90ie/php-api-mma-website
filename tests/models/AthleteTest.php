<?php


use PHPUnit\Framework\TestCase;


include_once "../helpers/Database.php";
include_once "../models/Athlete.php";

class AthleteTest extends TestCase
{
    private Athlete $athlete;
    private mysqli $db;

    // test data vars
    private int $idValid;
    private string $nameValid;
    private string $dobValid;
    private float $heightValid;
    private float $reachValid;
    private int $stanceIdValid;
    private int $idInvalid;
    private string $nameInvalid;
    private string $dobInvalid;
    private float $heightInvalid;
    private float $reachInvalid;
    private int $stanceIdInvalid;

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
        $this->db->close();
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->athlete->getId());
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

        // create new record in db
        $create_query = $this->athlete->create();
        // check that query ran successfully
        self::assertTrue($create_query);
        // check the object now has an id
        $id = $this->athlete->getId();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->athlete->delete();
        self::assertTrue($delete_query);
    }

    public function testGetOneValid()
    {
        $result = $this->athlete->getOne($this->idValid);

        self::assertTrue($result->num_rows == 1);

        $data = $result->fetch_assoc();

        self::assertEquals($data['AthleteID'], $this->athlete->getId());
        self::assertEquals($data['AthleteName'], $this->athlete->getName());
        self::assertEquals($data['AthleteHeightInCM'], $this->athlete->getHeight());
        self::assertEquals($data['AthleteReachInCM'], $this->athlete->getReach());
        self::assertEquals($data['AthleteStanceID'], $this->athlete->getStanceId());
        self::assertEquals($data['AthleteDOB'], $this->athlete->getDob());
    }

    public function testGetOneInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid Athlete ID");

        $this->athlete->getOne($this->idInvalid);
    }

    public function testGetAllAndGetResultsValid()
    {
        $results = $this->athlete->getAll();

        self::assertEquals($this->athlete->getResults(), $results);
        self::assertTrue($results->num_rows > 0);
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

        $new_athlete->create();

        $new_athlete_id = $new_athlete->getId();

        // use existing object to pero

        $new_name = "newValidName";
        $new_height = 123;
        $new_reach = 249;
        $new_stance_id = 3;
        $new_dob = DateTime::createFromFormat('d-m-Y', '14-12-1990')->format('Y-m-d');

        // update object vars
        $new_athlete->setName($new_name);
        $new_athlete->setHeight($new_height);
        $new_athlete->setReach($new_reach);
        $new_athlete->setStanceId($new_stance_id);
        $new_athlete->setDob($new_dob);

        // perform update
        $update_query = $new_athlete->update();
        self::assertTrue($update_query);

        // set another object to retrieve data for new_athlete to compare
        $this->athlete->getOne($new_athlete_id);

        self::assertEquals($new_athlete->getId(), $this->athlete->getId());
        self::assertEquals($new_athlete->getName(), $this->athlete->getName());
        self::assertEquals($new_athlete->getHeight(), $this->athlete->getHeight());
        self::assertEquals($new_athlete->getReach(), $this->athlete->getReach());
        self::assertEquals($new_athlete->getStanceId(), $this->athlete->getStanceId());
        self::assertEquals($new_athlete->getDob(), $this->athlete->getDob());

        // delete from db
        self::assertTrue($new_athlete->delete());
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
        $this->athlete->setId($this->idValid);
        self::assertEquals($this->idValid, $this->athlete->getId());
    }

    public function testGetIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->athlete->setId($this->idInvalid);
    }
}
