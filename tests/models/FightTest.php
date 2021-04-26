<?php


use PHPUnit\Framework\TestCase;

include_once "../helpers/Database.php";
include_once "../models/Fight.php";

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
        $this->db->close();
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->fight->getId());
        self::assertNull($this->fight->getEventId());
        self::assertNull($this->fight->getRefereeId());
        self::assertNull($this->fight->getTitleBout());
        self::assertNull($this->fight->getWeightClassId());
        self::assertNull($this->fight->getRounds());
        self::assertNull($this->fight->getResults());
    }

    public function testCreateAndDeleteValid()
    {
        // set object vars
        $this->fight->setEventID($this->eventIdValid);
        $this->fight->setRefereeId($this->refereeIdValid);
        $this->fight->setTitleBout($this->titleBoutValid);
        $this->fight->setWeightClassId($this->weightClassIdValid);
        $this->fight->setRounds($this->roundsValid);

        // create new record in db
        $create_query = $this->fight->create();
        // check that query ran successfully
        self::assertTrue($create_query);
        // check the object now has an id
        $id = $this->fight->getId();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->fight->delete();
        self::assertTrue($delete_query);
    }

    public function testGetOneValid()
    {
        self::assertTrue($this->fight->getOne($this->idValid));

        $results = $this->fight->getResults();

        self::assertTrue($results->num_rows == 1);

        $data = $results->fetch_assoc();

        self::assertEquals($data['FightID'], $this->fight->getId());
        self::assertEquals($data['EventID'], $this->fight->getEventId());
        self::assertEquals($data['RefereeID'], $this->fight->getRefereeId());
        self::assertEquals($data['TitleBout'], $this->fight->getTitleBout());
        self::assertEquals($data['WeightClassID'], $this->fight->getWeightClassId());
        self::assertEquals($data['NumOfRounds'], $this->fight->getRounds());
    }

    public function testGetOneInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid ID");

        $this->fight->getOne($this->idInvalid);
    }

    public function testGetAllAndGetResultsValid()
    {
        $results = $this->fight->getAll();

        self::assertEquals($this->fight->getResults(), $results);
        self::assertTrue($results->num_rows > 0);
    }

    public function testUpdate()
    {

        $new_fight = new Fight($this->db);

        // create object
        $new_fight->setEventID($this->eventIdValid);
        $new_fight->setRefereeId($this->refereeIdValid);
        $new_fight->setTitleBout($this->titleBoutValid);
        $new_fight->setWeightClassId($this->weightClassIdValid);
        $new_fight->setRounds($this->roundsValid);

        $new_fight->create();

        $new_fight_id = $new_fight->getId();

        // use existing object to pero

        $new_event_id = 3;
        $new_referee_id = 1;
        $new_title_bout = true;
        $new_weight_class_id = 3;
        $new_rounds = 5;

        // update object vars
        $new_fight->setEventID($new_event_id);
        $new_fight->setRefereeId($new_referee_id);
        $new_fight->setTitleBout($new_title_bout);
        $new_fight->setWeightClassId($new_weight_class_id);
        $new_fight->setRounds($new_rounds);

        // perform update
        $update_query = $new_fight->update();
        self::assertTrue($update_query);

        // set another object to retrieve data for new_fight to compare
        $this->fight->getOne($new_fight_id);

        self::assertEquals($new_fight->getId(), $this->fight->getId());
        self::assertEquals($new_fight->getEventId(), $this->fight->getEventId());
        self::assertEquals($new_fight->getRefereeId(), $this->fight->getRefereeId());
        self::assertEquals($new_fight->getTitleBout(), $this->fight->getTitleBout());
        self::assertEquals($new_fight->getWeightClassId(), $this->fight->getWeightClassId());
        self::assertEquals($new_fight->getRounds(), $this->fight->getRounds());

        // delete from db
        self::assertTrue($new_fight->delete());
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
        $this->fight->setRounds($this->roundsValid);
        self::assertEquals($this->roundsValid, $this->fight->getRounds());
    }

    public function testSetRoundsInvalidBelow()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight->setRounds($this->roundsInvalidBelow);
    }

    public function testSetRoundsInvalidAbove()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight->setRounds($this->roundsInvalidAbove);
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
        $this->fight->setId($this->idValid);
        self::assertEquals($this->idValid, $this->fight->getId());
    }

    public function testGetSetIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight->setId($this->idInvalid);
    }
}
