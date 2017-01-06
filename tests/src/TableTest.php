<?php
namespace ActiveRecord;

class TableTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformColumnToProperty_When_ColumnIdentifierSupplied_Expect_ColumnIdPrefixedWithUnderscore()
    {

        $table = new Table('activiteit', new Schema('\Test\Record', \ActiveRecord\Test\createMockPDO('', [])));

        $this->assertEquals("_id", $table->transformColumnToProperty('id'));
    }

    public function testSelect_When_NoWhereParametersSupplied_Expect_FiveRecords()
    {
        $schema = new Table('activiteit', new Schema('\Test\Record', \ActiveRecord\Test\createMockPDO('/SELECT id, name FROM activiteit/', [
            [],
            [],
            [],
            [],
            [],
        ])));

        $this->assertCount(5, $schema->select(['id', 'name'], []));
    }

    public function testSelect_When_SpecificWhereParameterSupplied_Expect_ThreeRecords()
    {
        $schema = new Table('activiteit', new Schema('\Test\Record', \ActiveRecord\Test\createMockPDO('/SELECT name FROM activiteit WHERE name = (?<namedParameter>:(\w+))/', [
                [],
                [],
                []
            ]
        )));

        $records = $schema->select(['name'], ['name' => 'foo']);

        $this->assertCount(3, $records);
    }

    public function testSelect_When_MultipleWhereParametersSupplied_Expect_OneRecord()
    {
        $schema = new Table('activiteit', new Schema('\Test\Record', \ActiveRecord\Test\createMockPDO('/SELECT name FROM activiteit WHERE name = (?<namedParameter1>:(\w+)) AND id = (?<namedParameter2>:(\w+))/', [
                [],
            ]
        )));

        $records = $schema->select(['name'], ['name' => 'foo', 'id' => '1']);

        $this->assertCount(1, $records);
    }

    public function testSelectFrom_When_NoWhereParametersSupplied_Expect_FiveRecords()
    {
        $schema = new Table('thema', new Schema('\Test\Record', \ActiveRecord\Test\createMockPDO('/SELECT id, name FROM activiteit/', [
                [],
                [],
                [],
                [],
                [],
            ]
        )));

        $this->assertCount(5, $schema->selectFrom('activiteit', ['id', 'name'], []));
    }

    public function testUpdate_When_NoWhereParametersSupplied_Expect_FiveUpdates()
    {
        $schema = new Table('activiteit', new Schema('\Test\Record', \ActiveRecord\Test\createMockPDOMultiple([
                '/UPDATE activiteit SET name = (?<namedSet1>:(\w+))/' => [],
                '/SELECT name FROM activiteit/' => [
                    [],
                    [],
                    [],
                    [],
                    [],
                ]
            ])
        ));

        $this->assertCount(5, $schema->update(['name' => 'newName'], []));
    }

    public function testUpdate_When_SpecificWhereParameterSupplied_Expect_ThreeUpdates()
    {
        $schema = new Table('activiteit', new Schema('\Test\Record', \ActiveRecord\Test\createMockPDOMultiple([
                '/UPDATE activiteit SET name = (?<namedSet1>:(\w+)) WHERE name = (?<namedParameter1>:(\w+)) AND id = (?<namedParameter2>:(\w+))/' => [],
                '/SELECT name FROM activiteit WHERE name = (?<namedParameter1>:(\w+)) AND id = (?<namedParameter2>:(\w+))/' => [
                    [],
                    [],
                    [],
                ]
            ])
        ));

        $this->assertCount(3, $schema->update(['name' => 'newName'], ['name' => 'oldName', 'id' => '1']));
    }

    public function testUpdate_When_MultipleWhereParameterSupplied_Expect_ThreeUpdates()
    {
        $schema = new Table('activiteit', new Schema('\Test\Record', \ActiveRecord\Test\createMockPDOMultiple([
                '/UPDATE activiteit SET name = (?<namedSet1>:(\w+)) WHERE name = (?<namedParameter1>:(\w+))/' => [],
                '/SELECT name FROM activiteit WHERE name = (?<namedParameter1>:(\w+))/' => [
                    [],
                    [],
                    [],
                ]
            ])
        ));

        $this->assertCount(3, $schema->update(['name' => 'newName'], ['name' => 'oldName']));
    }


    public function testDelete_When_SingleParameter_Expect_One()
    {
        $schema = new Table('activiteit', new Schema('\Test\Record', \ActiveRecord\Test\createMockPDOMultiple([
                '/DELETE FROM activiteit WHERE name = (?<namedSet1>:(\w+))/' => [],
                '/SELECT name FROM activiteit WHERE name = (?<namedParameter1>:(\w+))/' => [
                    [],
                ]
            ])
        ));

        $this->assertCount(1, $schema->delete(['name' => 'newName']));
    }


    public function testDelete_When_MultipleParameters_Expect_Five()
    {
        $schema = new Table('activiteit', new Schema('\Test\Record', \ActiveRecord\Test\createMockPDOMultiple([
                '/DELETE FROM activiteit WHERE id = (?<namedSet1>:(\w+)) AND name = (?<namedSet2>:(\w+))/' => [],
                '/SELECT id, name FROM activiteit WHERE id = (?<namedSet1>:(\w+)) AND name = (?<namedSet2>:(\w+))/' => [
                    [],
                    [],
                    [],
                    [],
                    [],
                ]
            ])
        ));

        $this->assertCount(5, $schema->delete(['id' => '1', 'name' => 'newName']));
    }

    public function testInsert_When_NoWhereParametersSupplied_Expect_InsertedRecord()
    {

        $schema = new Table('activiteit', new Schema('\Test\Record', \ActiveRecord\Test\createMockPDOMultiple([
                '/INSERT INTO activiteit \(id, name\) VALUES \((?<namedSet1>:(\w+)), (?<namedSet2>:(\w+))\)/' => [],
                '/SELECT id, name FROM activiteit WHERE id = (?<namedSet1>:(\w+)) AND name = (?<namedSet2>:(\w+))/' => [
                    [
                        'id' => '1',
                        'name' => 'newName'
                    ]
                ]
            ])
        ));

        $this->assertEquals('newName', $schema->insert(['id' => '1', 'name' => 'newName'])[0]->name);
    }
}