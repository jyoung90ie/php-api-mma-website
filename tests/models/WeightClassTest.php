<?php


use PHPUnit\Framework\TestCase;

include_once "../helpers/Database.php";
include_once "../models/WeightClass.php";

class WeightClassTest extends TestCase
{
    private WeightClass $weight_class;
    private mysqli $db;

    // test data vars
    private int $idValid;
    private string $weightClassValid;
    private int $weightMinValid;
    private int $weightMaxValid;
    private int $idInvalid;
    private int $weightMinInvalid;
    private int $weightMaxInvalid;

    // run before each test
    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        $this->weight_class = new WeightClass($this->db);

        // test data
        $this->idValid = 1;
        $this->weightClassValid = "weightClassValid";
        $this->weightMinValid = 100;
        $this->weightMaxValid = 500;

        $this->idInvalid = 0;
        $this->weightMinInvalid = 99;
        $this->weightMaxInvalid = 501;
    }

    // run after each test
    public function tearDown(): void
    {
        $this->db->close();
    }


    public function testDataStartsAsNull()
    {
        self::assertNull($this->weight_class->getId());
        self::assertNull($this->weight_class->getWeightClass());
        self::assertNull($this->weight_class->getMinWeight());
        self::assertNull($this->weight_class->getMaxWeight());
    }

    public function testCreateAndDeleteValid()
    {
        // set object vars
        $this->weight_class->setWeightClass($this->weightClassValid);
        $this->weight_class->setMinWeight($this->weightMinValid);
        $this->weight_class->setMaxWeight($this->weightMaxValid);

        // create new record in db
        $create_query = $this->weight_class->create();
        // check that query ran successfully
        self::assertTrue($create_query);
        // check the object now has an id
        $id = $this->weight_class->getId();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->weight_class->delete();
        self::assertTrue($delete_query);
    }

    public function testGetOneValid()
    {
        $result = $this->weight_class->getOne($this->idValid);

        self::assertTrue($result->num_rows == 1);

        $data = $result->fetch_assoc();

        self::assertEquals($data['WeightClassID'], $this->weight_class->getId());
        self::assertEquals($data['WeightClass'], $this->weight_class->getWeightClass());
        self::assertEquals($data['MinWeightInLB'], $this->weight_class->getMinWeight());
        self::assertEquals($data['MaxWeightInLB'], $this->weight_class->getMaxWeight());
    }

    public function testGetOneInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid Weight Class ID");

        $this->weight_class->getOne($this->idInvalid);
    }

    public function testGetAllAndGetResultsValid()
    {
        $results = $this->weight_class->getAll();

        self::assertEquals($this->weight_class->getResults(), $results);
        self::assertTrue($results->num_rows > 0);
    }

    public function testUpdate()
    {
        $new_weight_class = new WeightClass($this->db);

        // create object
        $new_weight_class->setWeightClass($this->weightClassValid);
        $new_weight_class->setMinWeight($this->weightMinValid);
        $new_weight_class->setMaxWeight($this->weightMaxValid);

        $new_weight_class->create();

        $new_weight_class_id = $new_weight_class->getId();

        // use existing object to pero

        $new_description = "newWeightClassDescription";
        $new_weight_min = 120;
        $new_weight_max = 135;

        // update object vars
        $new_weight_class->setWeightClass($new_description);
        $new_weight_class->setMinWeight($new_weight_min);
        $new_weight_class->setMaxWeight($new_weight_max);

        // perform update
        $update_query = $new_weight_class->update();
        self::assertTrue($update_query);

        // set another object to retrieve data for new_athlete to compare
        $this->weight_class->getOne($new_weight_class_id);

        self::assertEquals($new_weight_class->getId(), $this->weight_class->getId());
        self::assertEquals($new_weight_class->getWeightClass(), $this->weight_class->getWeightClass());
        self::assertEquals($new_weight_class->getMinWeight(), $this->weight_class->getMinWeight());
        self::assertEquals($new_weight_class->getMaxWeight(), $this->weight_class->getMaxWeight());

        // delete from db
        self::assertTrue($new_weight_class->delete());
    }

    public function testGetSetWeightMinValid()
    {
        $this->weight_class->setMinWeight($this->weightMinValid);
        self::assertEquals($this->weightMinValid, $this->weight_class->getMinWeight());
    }

    public function testGetSetWeightMinInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->weight_class->setMinWeight($this->weightMinInvalid);
    }

    public function testGetSetWeightMaxValid()
    {
        $this->weight_class->setMaxWeight($this->weightMaxValid);
        self::assertEquals($this->weightMaxValid, $this->weight_class->getMaxWeight());
    }

    public function testSetWeightMaxInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->weight_class->setMaxWeight($this->weightMaxInvalid);
    }

    public function testGetSetWeightClassValid()
    {
        $this->weight_class->setWeightClass($this->weightClassValid);
        self::assertEquals($this->weightClassValid, $this->weight_class->getWeightClass());
    }

    public function testGetSetIdValid()
    {
        $this->weight_class->setId($this->idValid);
        self::assertEquals($this->idValid, $this->weight_class->getId());
    }

    public function testSetIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->weight_class->setId($this->idInvalid);
    }

}
