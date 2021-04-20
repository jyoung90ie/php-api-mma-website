<?php


use PHPUnit\Framework\TestCase;

include_once "../helpers/Database.php";
include_once "../models/FightResult.php";

class FightResultTest extends TestCase
{
    private FightResult $fight_result;
    private mysqli $db;

    // test data vars
    private int $idValid;
    private int $fightIdValid;
    private int $resultIdValid;
    private int $winnerIdValid;
    private int $idInvalid;
    private int $fightIdInvalid;
    private int $resultIdInvalid;
    private int $winnerIdInvalid;

    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        $this->fight_result = new FightResult($this->db);

        // test data
        $this->idValid = 1;
        $this->fightIdValid = 2;
        $this->resultIdValid = 3;
        $this->winnerIdValid = 4;

        $this->idInvalid = 0;
        $this->fightIdInvalid = -1;
        $this->resultIdInvalid = -1;
        $this->winnerIdInvalid = -1;
    }

    // run after each test
    public function tearDown(): void
    {
        $this->db->close();
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->fight_result->getId());
        self::assertNull($this->fight_result->getFightId());
        self::assertNull($this->fight_result->getResultId());
        self::assertNull($this->fight_result->getWinnerId());
        self::assertNull($this->fight_result->getResults());
    }

    public function testCreateAndDeleteValid()
    {
        // set object vars
        $this->fight_result->setFightId($this->fightIdValid);
        $this->fight_result->setResultId($this->resultIdValid);
        $this->fight_result->setWinnerId($this->winnerIdValid);

        // create new record in db
        $create_query = $this->fight_result->create();
        // check that query ran successfully
        self::assertTrue($create_query);
        // check the object now has an id
        $id = $this->fight_result->getId();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->fight_result->delete();
        self::assertTrue($delete_query);
    }

    public function testGetOneValid()
    {
        $result = $this->fight_result->getOne($this->idValid);

        self::assertTrue($result->num_rows == 1);

        $data = $result->fetch_assoc();

        self::assertEquals($data['FightResultID'], $this->fight_result->getId());
        self::assertEquals($data['FightID'], $this->fight_result->getFightId());
        self::assertEquals($data['ResultTypeID'], $this->fight_result->getResultId());
        self::assertEquals($data['WinnerAthleteID'], $this->fight_result->getWinnerId());
    }

    public function testGetOneInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid ID");

        $this->fight_result->getOne($this->idInvalid);
    }

    public function testGetAllAndGetResultsValid()
    {
        $results = $this->fight_result->getAll();

        self::assertEquals($this->fight_result->getResults(), $results);
        self::assertTrue($results->num_rows > 0);
    }

    public function testUpdate()
    {
        $new_result = new FightResult($this->db);

        // create object
        $new_result->setFightId($this->fightIdValid);
        $new_result->setResultId($this->resultIdValid);
        $new_result->setWinnerId($this->winnerIdValid);

        $new_result->create();

        $new_fight_result_id = $new_result->getId();

        // use existing object to pero

        $new_fight_id = 10;
        $new_result_id = 5;
        $new_winner_id = 20;

        // update object vars
        $new_result->setFightId($new_fight_id);
        $new_result->setFightId($new_result_id);
        $new_result->setWinnerId($new_winner_id);

        // perform update
        $update_query = $new_result->update();
        self::assertTrue($update_query);

        // set another object to retrieve data for new_athlete to compare
        $this->fight_result->getOne($new_fight_result_id);

        self::assertEquals($new_result->getId(), $this->fight_result->getId());
        self::assertEquals($new_result->getFightId(), $this->fight_result->getFightId());
        self::assertEquals($new_result->getResultId(), $this->fight_result->getResultId());
        self::assertEquals($new_result->getWinnerId(), $this->fight_result->getWinnerId());

        // delete from db
        self::assertTrue($new_result->delete());
    }

    public function testGetSetResultIdValid()
    {
        $this->fight_result->setResultId($this->resultIdValid);
        self::assertEquals($this->resultIdValid, $this->fight_result->getResultId());
    }

    public function testGetSetDateInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight_result->setResultId($this->resultIdInvalid);
    }

    public function testGetSetFightIdValid()
    {
        $this->fight_result->setFightId($this->fightIdValid);
        self::assertEquals($this->fightIdValid, $this->fight_result->getFightId());
    }

    public function testGetSetFightIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight_result->setFightId($this->fightIdInvalid);
    }

    public function testGetSetWinnerIdValid()
    {
        $this->fight_result->setWinnerId($this->winnerIdValid);
        self::assertEquals($this->winnerIdValid, $this->fight_result->getWinnerId());
    }

    public function testGetSetWinnerIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight_result->setWinnerId($this->winnerIdInvalid);
    }

    public function testGetSetIdValid()
    {
        $this->fight_result->setId($this->idValid);
        self::assertEquals($this->idValid, $this->fight_result->getId());
    }

    public function testSetIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->fight_result->setId($this->idInvalid);
    }

}
