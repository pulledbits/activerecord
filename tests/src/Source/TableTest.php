<?php
namespace ActiveRecord\Source;

class TableTest extends \PHPUnit_Framework_TestCase
{

    private $object;

    protected function setUp()
    {
        $this->object = new Table('\\Database\\Record');
    }

    public function testDescribe_When_DefaultState_Expect_ClassDescription()
    {
        $mockTable = \ActiveRecord\Test\createMockTable('MyTable', [
            'name' => [
                'primaryKey' => true
            ],
            'birthdate' => [
                'primaryKey' => true
            ],
            'address' => [
                'primaryKey' => false
            ],

            'role_id' => [
                'primaryKey' => false,
                'references' => [
                    'fk_othertable_role' => ['OtherTable', 'id']
                ]
            ],
            'role2_id' => [
                'primaryKey' => false,
                'references' => [
                    'fk_anothertable_role' => ['AntoherTable', 'id']
                ]
            ],
            'extra_column_id' => [
                'primaryKey' => false,
                'references' => [
                    'fk_anothertable_role' => ['AntoherTable', 'column_id']
                ]
            ],
        ]);

        $classDescription = $this->object->describe($mockTable);
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable');
        $this->assertEquals($classDescription['interfaces'][0], '\\ActiveRecord\\WritableRecord');
        $this->assertEquals($classDescription['traits'][0], '\\ActiveRecord\\Record\\WritableTrait');


        $this->assertCount(0, $classDescription['methods']['primaryKey']['parameters']);
        $this->assertEquals('return [\'name\' => $this->values[\'name\'], \'birthdate\' => $this->values[\'birthdate\']];', $classDescription['methods']['primaryKey']['body'][0]);

        $this->assertEquals($classDescription['methods']['fetchByFkOthertableRole']['parameters'], []);
        $this->assertFalse($classDescription['methods']['fetchByFkOthertableRole']['static']);

        $this->assertEquals('return $this->table->selectFrom("OtherTable", [\'id\'], [', $classDescription['methods']['fetchByFkOthertableRole']['body'][0]);
        $this->assertEquals(join(',' . PHP_EOL, ['\'id\' => $this->values[\'role_id\']']), $classDescription['methods']['fetchByFkOthertableRole']['body'][1]);
        $this->assertEquals(']);', $classDescription['methods']['fetchByFkOthertableRole']['body'][2]);

        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['parameters'], []);
        $this->assertFalse($classDescription['methods']['fetchByFkAnothertableRole']['static']);

        $this->assertEquals('return $this->table->selectFrom("AntoherTable", [\'id\', \'column_id\'], [', $classDescription['methods']['fetchByFkAnothertableRole']['body'][0]);
        $this->assertEquals(join(',' . PHP_EOL, ['\'id\' => $this->values[\'role2_id\']', '\'column_id\' => $this->values[\'extra_column_id\']']), $classDescription['methods']['fetchByFkAnothertableRole']['body'][1]);
        $this->assertEquals(']);', $classDescription['methods']['fetchByFkAnothertableRole']['body'][2]);
    }
    
    public function testDescribe_When_DifferingTableName_Expect_ArrayWithClassIdentifierAndDifferentClassName()
    {
        $dbalTable = \ActiveRecord\Test\createMockTable('MyTable2', []);
        $classDescription = $this->object->describe($dbalTable);
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable2');
    }

    public function testDescribe_When_ViewUsed_Expect_ArrayWithReadableRecord()
    {
        $dbalView = \ActiveRecord\Test\createMockView('MyView', 'CREATE VIEW `MyView` AS
  SELECT
    `schema`.`MyTable`.`name`   AS `name`,
    `schema`.`MyTable`.`birthdate` AS `birthdate`
  FROM `teach`.`thema`;');
        $classDescription = $this->object->describe($dbalView);
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyView');
        $this->assertEquals($classDescription['interfaces'][0], '\\ActiveRecord\\ReadableRecord');
        $this->assertEquals($classDescription['traits'][0], '\\ActiveRecord\\Record\\ReadableTrait');
    }
}