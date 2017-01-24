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
        $this->assertEquals(['name', 'birthdate'], $classDescription['identifier']);
        $this->assertEquals([
            'FkOthertableRole' => [
                'table' => 'OtherTable',
                'where' => [
                    'id' => 'role_id'
                ],
            ],
            'FkAnothertableRole' => [
                'table' => 'AntoherTable',
                'where' => [
                    'id' => 'role2_id',
                    'column_id' => 'extra_column_id'
                ],
            ]
        ], $classDescription['references']);
    }

    public function testDescribe_When_ViewUsed_Expect_ArrayWithReadableRecord()
    {
        $dbalView = \ActiveRecord\Test\createMockView('MyView', 'CREATE VIEW `MyView` AS
  SELECT
    `schema`.`MyTable`.`name`   AS `name`,
    `schema`.`MyTable`.`birthdate` AS `birthdate`
  FROM `teach`.`thema`;');
        $classDescription = $this->object->describe($dbalView);
        $this->assertEquals([], $classDescription['identifier']);
        $this->assertEquals([], $classDescription['references']);
    }
}