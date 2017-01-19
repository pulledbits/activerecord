<?php
namespace ActiveRecord;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Table
     */
    private $object;

    protected function setUp()
    {
        $this->object = new View('leerdoelenview', new Schema('\Test\Record', \ActiveRecord\Test\createMockPDOMultiple([
            '/^SELECT name, birthdate FROM leerdoelenview$/' => [
                [],
                [],
                [],
                [],
                [],
            ],
            '/^SELECT name, birthdate FROM leerdoelenview WHERE name = :\w+ AND birthdate = :\w+$/' => [
                [
                    'name' => 'Alice'
                ]
            ],
            '/^SELECT id, name FROM thema$/' => [
                [],
                [],
                [],
                [],
                [],
            ],
        ])));
    }

    public function testCRUD_When_DefaultState_Expect_RecordCreatedSelectedUpdatedAndDeleted() {
        $records = $this->object->select(['name', 'birthdate'], ['name' => 'Alice', 'birthdate' => '1984-06-03']);
        $this->assertCount(1, $records, '1 record exists');
        $this->assertEquals('Alice', $records[0]->name, 'record is properly updated');
    }

    public function testSelect_When_NoWhereParametersSupplied_Expect_FiveRecords()
    {
        $records = $this->object->select(['name', 'birthdate'], []);
        $this->assertCount(5, $records);
    }

    public function testSelectFrom_When_NoWhereParametersSupplied_Expect_FiveRecords()
    {
        $records = $this->object->selectFrom('thema', ['id', 'name'], []);
        $this->assertCount(5, $records);
        $this->assertInstanceOf('\Test\Record\thema', $records[0]);
    }

}