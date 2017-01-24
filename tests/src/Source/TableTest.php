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
        $this->assertEquals('\\ActiveRecord\\MetaRecord', $classDescription['interfaces'][0]);
        $this->assertEquals(['name', 'birthdate'], $classDescription['recordIdentifier']);

        $this->assertCount(0, $classDescription['methods']['identifier']['parameters']);
        $this->assertEquals('return [\'name\', \'birthdate\'];', $classDescription['methods']['identifier']['body'][0]);

        $l = 0;
        $this->assertEquals('return [', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals("\t" . '\'FkOthertableRole\' => [', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals("\t\t" . '\'table\' => \'OtherTable\', ', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals("\t\t" . '\'where\' => [', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals("\t\t\t" . '\'id\' => \'role_id\'', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals("\t\t" . ']', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals("\t" . '], ', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals("\t" . '\'FkAnothertableRole\' => [', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals("\t\t" . '\'table\' => \'AntoherTable\', ', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals("\t\t" . '\'where\' => [', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals("\t\t\t" . '\'id\' => \'role2_id\', ', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals("\t\t\t" . '\'column_id\' => \'extra_column_id\'', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals("\t\t" . ']', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals("\t" . ']', $classDescription['methods']['references']['body'][$l++]);
        $this->assertEquals('];', $classDescription['methods']['references']['body'][$l++]);
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
    }
}