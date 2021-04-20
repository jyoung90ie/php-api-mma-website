<?php


use PHPUnit\Framework\TestCase;

include_once "../helpers/Database.php";
include_once "../models/FightAthlete.php";

class FightAthleteTest extends TestCase
{
    private FightAthlete $fight_athlete;
    private mysqli $db;

    // test data vars
    private int $idValid;
    private int $fightIdValid;
    private int $athleteIdValid;
    private int $idInvalid;
    private int $fightIdInvalid;
    private int $athleteIdInvalid;

    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        $this->fight_athlete = new FightAthlete($this->db);

        // test data
        $this->idValid = 1;
        $this->fightIdValid = 2;
        $this->athleteIdValid = 3;

        $this->idInvalid = 0;
        $this->fightIdInvalid = -1;
        $this->athleteIdInvalid = 0;
    }

    // run after each test
    public function tearDown(): void
    {
        $this->db->close();
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->fight_athlete->getId());
        self::assertNull($this->fight_athlete->getFightId());
        self::assertNull($this->fight_athlete->getAthleteId());
    }

    public function testCreateAndDeleteValid()
    {
        // set object vars
        $this->fight_athlete->setFightId($this->fightIdValid);
        $this->fight_athlete->setAthleteId($this->athleteIdValid);

        // create new record in db
        $create_query = $this->fight_athlete->create();
        // check that query ran successfully
        self::assertTrue($create_query);
        // check the object now has an id
        $id = $this->fight_athlete->getId();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->fight_athlete->delete();
        self::assertTrue($delete_query);
    }

    public function testGetOneValid()
    {
        $result = $this->fight_athlete->getOne($this->idValid);

        self::assertTrue($result->num_rows == 1);

        $data = $result->fetch_assoc();

        self::assertEquals($data['FightAthleteID'], $this->fight_athlete->getId());
        self::assertEquals($data['FightID'], $this->fight_athlete->getFightId());
        self::assertEquals($data['AthleteID'], $this->fight_athlete->getAthleteId());
    }

    public function testGetOneInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid ID");

        $this->fight_athlete->getOne($this->idInvalid);
    }

    public function testGetAllAndGetResultsValid()
    {
        $results = $this->fight_athlete->getAll();

        self::assertEquals($this->fight_athlete->getResults(), $results);
        self::assertTrue($results->num_rows > 0);
    }

    public function testUpdate()
    {
        $new_fight_athlete = new FightAthlete($this->db);

        // create object
        $new_fight_athlete->setFightId($this->fightIdValid);
        $new_fight_athlete->setAthleteId($this->athleteIdValid);

        $new_fight_athlete->create();

        $new_fight_athlete_id = $new_fight_athlete->getId();

        // use existing object to pero

        $new_fight_id = 123;
        $new_athlete_id = 123;

        // update object vars
        $new_fight_athlete->setFightId($new_fight_id);
        $new_fight_athlete->setAthleteId($new_athlete_id);

        // perform update
        $update_query = $new_fight_athlete->update();
        self::assertTrue($update_query);

        // set another object to retrieve data for new_athlete to compare
        $this->fight_athlete->getOne($new_fight_athlete_id);

        self::assertEquals($new_fight_athlete->getId(), $this->fight_athlete->getId());
        self::assertEquals($new_fight_athlete->getFightId(), $this->fight_athlete->getFightId());
        self::assertEquals($new_fight_athlete->getAthleteId(), $this->fight_athlete->getAthleteId());

        // delete from db
        self::assertTrue($new_fight_athlete->delete());
    }

    public function testGetSetAthleteId()
    {
        $this->fight_athlete->setAthleteId($this->athleteIdValid);
        self::assertEquals($this->athleteIdValid, $this->fight_athlete->getAthleteId());
    }

    public function testGetSetAthleteIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight_athlete->setAthleteId($this->fightIdInvalid);
    }

    public function testSetAthleteIdBelowMinInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight_athlete->setAthleteId($this->athleteIdInvalid);
    }

    public function testGetSetFightIdValid()
    {
        $this->fight_athlete->setFightId($this->fightIdValid);
        self::assertEquals($this->fightIdValid, $this->fight_athlete->getFightId());
    }

    public function testGetSetIdValid()
    {
        $this->fight_athlete->setId($this->idValid);
        self::assertEquals($this->idValid, $this->fight_athlete->getId());
    }

    public function testSetIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight_athlete->setId($this->idInvalid);
    }

}
