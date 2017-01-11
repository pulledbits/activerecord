<?php
namespace ActiveRecord;

class TableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Table
     */
    private $object;

    protected function setUp()
    {
        $this->object = new Table('activiteit', new Schema('\Test\Record', \ActiveRecord\Test\createMockPDOMultiple([
            '/^SELECT id, name FROM activiteit$/' => [
                [],
                [],
                [],
                [],
                [],
            ],
            '/DELETE FROM activiteit WHERE name = (?<namedSet1>:(\w+))/' => [],
            '/^UPDATE activiteit SET name = (?<namedSet1>:(\w+)) WHERE name = (?<namedParameter1>:(\w+))$/' => [],
            '/^SELECT name FROM activiteit WHERE name = (?<namedParameter>:(\w+))$/' => [
                [],
                [],
                []
            ],
            '/^UPDATE activiteit SET name = (?<namedSet1>:(\w+)) WHERE name = (?<namedParameter1>:(\w+)) AND id = (?<namedParameter2>:(\w+))$/' => [],
            '/^SELECT name FROM activiteit WHERE name = (?<namedParameter1>:(\w+)) AND id = (?<namedParameter2>:(\w+))$/' => [
                [],
            ],
            '/^SELECT id, name FROM thema$/' => [
                [],
                [],
                [],
                [],
                [],
            ],
            '/^UPDATE activiteit SET name = (?<namedSet1>:(\w+))$/' => [],
            '/^SELECT name FROM activiteit$/' => [
                [],
                [],
                [],
                [],
                [],
            ],
            '/^INSERT INTO activiteit \(id, name\) VALUES \((?<namedSet1>:(\w+)), (?<namedSet2>:(\w+))\)$/' => [],
            '/^DELETE FROM activiteit WHERE id = (?<namedSet1>:(\w+)) AND name = (?<namedSet2>:(\w+))$/' => [],
            '/^SELECT id, name FROM activiteit WHERE id = (?<namedSet1>:(\w+)) AND name = (?<namedSet2>:(\w+))$/' => [
                [
                    'id' => '1',
                    'name' => 'newName'],
                [],
                [],
                [],
                [],
            ]
        ])));
    }

    public function testSelect_When_NoWhereParametersSupplied_Expect_FiveRecords()
    {
        $records = $this->object->select(['id', 'name'], []);
        $this->assertCount(5, $records);
    }

    public function testSelect_When_SpecificWhereParameterSupplied_Expect_ThreeRecords()
    {
        $records = $this->object->select(['name'], ['name' => 'foo']);
        $this->assertCount(3, $records);
    }

    public function testSelect_When_MultipleWhereParametersSupplied_Expect_OneRecord()
    {
        $records = $this->object->select(['name'], ['name' => 'foo', 'id' => '1']);
        $this->assertCount(1, $records);
    }

    public function testSelectFrom_When_NoWhereParametersSupplied_Expect_FiveRecords()
    {
        $records = $this->object->selectFrom('thema', ['id', 'name'], []);
        $this->assertCount(5, $records);
    }

    public function testUpdate_When_NoWhereParametersSupplied_Expect_FiveUpdates()
    {
        $records = $this->object->update(['name' => 'newName'], []);
        $this->assertCount(5, $records);
    }

    public function testUpdate_When_SpecificWhereParameterSupplied_Expect_ThreeUpdates()
    {
        $records = $this->object->update(['name' => 'newName'], ['name' => 'oldName', 'id' => '1']);
        $this->assertCount(1, $records);
    }

    public function testUpdate_When_MultipleWhereParameterSupplied_Expect_ThreeUpdates()
    {
        $records = $this->object->update(['name' => 'newName'], ['name' => 'oldName']);
        $this->assertCount(3, $records);
    }


    public function testDelete_When_SingleParameter_Expect_One()
    {
        $records = $this->object->delete(['name' => 'newName']);
        $this->assertCount(3, $records);
    }

    public function testDelete_When_MultipleParameters_Expect_Five()
    {
        $records = $this->object->delete(['id' => '1', 'name' => 'newName']);
        $this->assertCount(5, $records);
    }

    public function testInsert_When_NoWhereParametersSupplied_Expect_InsertedRecord()
    {
        $records = $this->object->insert(['id' => '1', 'name' => 'newName']);
        $this->assertEquals('newName', $records[0]->name);
    }
}