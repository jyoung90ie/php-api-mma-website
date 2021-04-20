<?php


use PHPUnit\Framework\TestCase;

include_once "../helpers/Database.php";
include_once "../models/Permission.php";

class PermissionTest extends TestCase
{
    private Permission $permission;
    private mysqli $db;

    // test data vars
    private int $idValid;
    private string $descriptionValid;
    private string $areaValid;
    private string $typeValid;
    private int $idInvalid;
    private string $descriptionInvalid;
    private string $areaInvalid;
    private string $typeInvalid;

    public function setUp(): void
    {
        $this->db = (new Database())->getConnection();
        $this->permission = new Permission($this->db);

        // test data
        $this->idValid = 1;
        $this->descriptionValid = 'descriptionValid';
        $this->areaValid = 'FIGHTS';
        $this->typeValid = 'CREATE';

        $this->idInvalid = 0;
        $this->descriptionInvalid = 'fail';
        $this->areaInvalid = 'INVALID';
        $this->typeInvalid = 'FAIL';
    }

    // run after each test
    public function tearDown(): void
    {
        $this->db->close();
    }

    public function testDataStartsAsNull()
    {
        self::assertNull($this->permission->getId());
        self::assertNull($this->permission->getDescription());
        self::assertNull($this->permission->getArea());
        self::assertNull($this->permission->getType());
    }

    public function testCreateAndDeleteValid()
    {
        // set object vars
        $this->permission->setDescription($this->descriptionValid);
        $this->permission->setArea($this->areaValid);
        $this->permission->setType($this->typeValid);

        // create new record in db
        $create_query = $this->permission->create();
        // check that query ran successfully
        self::assertTrue($create_query);
        // check the object now has an id
        $id = $this->permission->getId();
        self::assertNotNull($id);

        // delete object
        $delete_query = $this->permission->delete();
        self::assertTrue($delete_query);
    }

    public function testGetOneInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid ID");

        $this->permission->getOne($this->idInvalid);
    }

    public function testGetAllAndGetResultsValid()
    {

        $results = $this->permission->getAll();

        self::assertEquals($this->permission->getResults(), $results);
        self::assertTrue($results->num_rows > 0);
    }

    public function testUpdate()
    {
        $new_permission = new Permission($this->db);

        // create object
        $new_permission->setDescription($this->descriptionValid);
        $new_permission->setType($this->typeValid);
        $new_permission->setArea($this->areaValid);

        $new_permission->create();

        $new_id = $new_permission->getId();

        // use existing object to pero

        $new_description = "newValidDescription";
        $new_area = "COMMENTS";
        $new_type = "DELETE";

        // update object vars
        $new_permission->setDescription($new_description);
        $new_permission->setArea($new_area);
        $new_permission->setType($new_type);

        // perform update
        $update_query = $new_permission->update();
        self::assertTrue($update_query);

        // set another object to retrieve data for new_athlete to compare
        $this->permission->getOne($new_id);

        self::assertEquals($new_permission->getId(), $this->permission->getId());
        self::assertEquals($new_permission->getDescription(), $this->permission->getDescription());
        self::assertEquals($new_permission->getArea(), $this->permission->getArea());
        self::assertEquals($new_permission->getType(), $this->permission->getType());

        // delete from db
        self::assertTrue($new_permission->delete());
    }

    public function testGetSetDescriptionValid()
    {
        $this->permission->setDescription($this->descriptionValid);
        self::assertEquals($this->descriptionValid, $this->permission->getDescription());
    }

    public function testGetSetDescriptionInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->permission->setDescription($this->descriptionInvalid);
    }
    public function testGetSetAreaValid()
    {
        $this->permission->setArea($this->areaValid);
        self::assertEquals($this->areaValid, $this->permission->getArea());
    }

    public function testGetSetAreaInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->permission->setArea($this->areaInvalid);
    }
    public function testGetSetTypeValid()
    {
        $this->permission->setType($this->typeValid);
        self::assertEquals($this->typeValid, $this->permission->getType());
    }

    public function testGetSetTypeInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->permission->setType($this->typeInvalid);
    }

    public function testGetSetIdValid()
    {
        $this->permission->setId($this->idValid);
        self::assertEquals($this->idValid, $this->permission->getId());
    }

    public function testSetIdInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->permission->setId($this->idInvalid);
    }
}
