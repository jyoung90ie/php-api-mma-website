<?php

namespace models;

include_once '../../helpers/config.php';
include_once '../../autoload.php';

use helpers\Database;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;


class FightTest extends TestCase
{
    private $fight;
    private $db;

    private $idValid;
    private $eventIdValid;
    private $refereeIdValid;
    private $titleBoutValid;
    private $weightClassIdValid;
    private $roundsValid;
    private $idInvalid;
    private $eventIdInvalid;
    private $refereeIdInvalid;
    private $titleBoutInvalid;
    private $weightClassIdInvalid;
    private $roundsInvalidBelow;
    private $roundsInvalidAbove;

    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        $this->fight = new Fight($this->db);

        // test data
        $this->idValid = 1;
        $this->eventIdValid = 2;
        $this->refereeIdValid = 3;
        $this->titleBoutValid = false;
        $this->weightClassIdValid = 3;
        $this->roundsValid = 3;

        $this->idInvalid = 0;
        $this->eventIdInvalid = 0;
        $this->refereeIdInvalid = 0;
        $this->titleBoutInvalid = 'true';
        $this->weightClassIdInvalid = 0;
        $this->roundsInvalidBelow = 2;
        $this->roundsInvalidAbove = 6;
    }

    // run after each test
    public function tearDown(): void
    {
        $this->db = null;
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->fight->getFightId());
        self::assertNull($this->fight->getEventId());
        self::assertNull($this->fight->getRefereeId());
        self::assertNull($this->fight->getTitleBout());
        self::assertNull($this->fight->getWeightClassId());
        self::assertNull($this->fight->getNumOfRounds());
        self::assertNull($this->fight->getResults());
    }

    public function testCreateAndDeleteValid()
    {
        // set object vars
        $this->fight->setEventID($this->eventIdValid);
        $this->fight->setRefereeId($this->refereeIdValid);
        $this->fight->setTitleBout($this->titleBoutValid);
        $this->fight->setWeightClassId($this->weightClassIdValid);
        $this->fight->setNumOfRounds($this->roundsValid);
 
        // create new record in db
        $create_query = $this->fight->create();
        // check that query ran successfully
        self::assertTrue($create_query);
        // check the object now has an id
        $id = $this->fight->getFightId();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->fight->delete($id);
        self::assertTrue($delete_query);
    }

    public function testGetOneValid()
    {
        self::assertTrue($this->fight->getOne($this->idValid));

        $results = $this->fight->getResults();

        self::assertTrue($results->num_rows == 1);

        $data = $results->fetch_assoc();

        self::assertEquals($data['FightID'], $this->fight->getFightId());
        self::assertEquals($data['EventID'], $this->fight->getEventId());
        self::assertEquals($data['RefereeID'], $this->fight->getRefereeId());
        self::assertEquals($data['TitleBout'], $this->fight->getTitleBout());
        self::assertEquals($data['WeightClassID'], $this->fight->getWeightClassId());
        self::assertEquals($data['NumOfRounds'], $this->fight->getNumOfRounds());
    }

    public function testGetOneInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid ID");

        $this->fight->getOne($this->idInvalid);
    }

    public function testGetAllAndGetResultsValid()
    {
        $results = $this->fight->getAll(10, 0);

        self::assertEquals($this->fight->getResults(), $results);
        self::assertTrue(sizeof($results) > 0);
    }

    public function testUpdate()
    {


        $data = ["EventID" => 524,
            "RefereeID" => 15,
            "TitleBout" => 0,
            "WeightClassID" => 2,
            "NumOfRounds" => 3,
            "AthleteID1" => 25,
            "AthleteID2" => 34,
            "FightAthleteID1" => 10370,
            "FightAthleteID2" => 10371];

        // perform update
        $update_query = $this->fight->update(5202, $data);
        self::assertTrue($update_query > 0);

    }


    public function testSetTitleBoutValid()
    {
        $this->fight->setTitleBout($this->titleBoutValid);
        self::assertEquals($this->titleBoutValid, $this->fight->getTitleBout());
    }

    public function testSetWeightClassIdValid()
    {
        $this->fight->setWeightClassId($this->weightClassIdValid);
        self::assertEquals($this->weightClassIdValid, $this->fight->getWeightClassId());
    }

    public function testSetWeightClassIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight->setWeightClassId($this->weightClassIdInvalid);
    }

    public function testSetRoundsValid()
    {
        $this->fight->setNumOfRounds($this->roundsValid);
        self::assertEquals($this->roundsValid, $this->fight->getNumOfRounds());
    }

    public function testSetRoundsInvalidBelow()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight->setNumOfRounds($this->roundsInvalidBelow);
    }

    public function testSetRoundsInvalidAbove()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight->setNumOfRounds($this->roundsInvalidAbove);
    }

    public function testGetSetRefereeIdValid()
    {
        $this->fight->setRefereeId($this->refereeIdValid);
        self::assertEquals($this->refereeIdValid, $this->fight->getRefereeId());
    }

    public function testGetSetRefereeIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight->setRefereeId($this->refereeIdInvalid);
    }

    public function testGetSetEventIDValid()
    {
        $this->fight->setEventID($this->eventIdValid);
        self::assertEquals($this->eventIdValid, $this->fight->getEventId());
    }

    public function testGetSetEventIDInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight->setEventID($this->eventIdInvalid);
    }

    public function testGetSetIdValid()
    {
        $this->fight->setFightId($this->idValid);
        self::assertEquals($this->idValid, $this->fight->getFightId());
    }

    public function testGetSetIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight->setFightId($this->idInvalid);
    }
}
