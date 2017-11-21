<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:36
 */

namespace pulledbits\ActiveRecord\SQL\Meta;

class SchemaTest extends \PHPUnit_Framework_TestCase
{

    public function testDescribe_When_Default_Expect_ArrayWithClasses()
    {
        $schema = \pulledbits\ActiveRecord\Test\createMockSchema([
            'MyTable' => [
                'extra_column_id' => [
                    'primaryKey' => false,
                    'auto_increment' => false,
                    'required' => false,
                    'references' => [
                        'fk_anothertable_role' => ['AnotherTable', 'column_id']
                    ]
                ]
            ],
            'AnotherTable' => []
        ]);

        $schemaDescription = $schema->describeTables(new Table());

        $this->assertEquals([
            'FkAnothertableRole' => [
                'table' => 'AnotherTable',
                'where' => [
                    'column_id' => 'extra_column_id'
                ],
            ]
        ], $schemaDescription['MyTable']['references']);
        $this->assertEquals([
            'FkAnothertableRole' => [
                'table' => 'MyTable',
                'where' => [
                    'extra_column_id' => 'column_id'
                ],
            ]
        ], $schemaDescription['AnotherTable']['references']);
    }


    public function testDescribe_When_ViewAvailable_Expect_ArrayWithReadableClasses()
    {
        $schema = \pulledbits\ActiveRecord\Test\createMockSchema([
            'MyView' => 'CREATE VIEW `MyView` AS
  SELECT
    `schema`.`MyTable`.`name`   AS `name`,
    `schema`.`MyTable`.`birthdate` AS `birthdate`
  FROM `teach`.`thema`;'
        ]);

        $schemaDescription = $schema->describeTables(new Table());
        $this->assertArrayHasKey('MyView', $schemaDescription);
        $this->assertEquals([], $schemaDescription['MyView']['identifier']);
        $this->assertEquals([], $schemaDescription['MyView']['requiredAttributeIdentifiers']);
        $this->assertEquals([], $schemaDescription['MyView']['references']);
    }

    public function testDescribe_When_ViewUsedWithExistingTableIdentifier_Expect_EntityTypeIdentifier()
    {
        $schema = \pulledbits\ActiveRecord\Test\createMockSchema([
            'MyTable' => [
                'name' => [
                    'primaryKey' => true,
                    'auto_increment' => true,
                    'required' => true
                ],
                'birthdate' => [
                    'primaryKey' => true,
                    'auto_increment' => false,
                    'required' => true
                ],
                'address' => [
                    'primaryKey' => false,
                    'auto_increment' => false,
                    'required' => false
                ],

                'role_id' => [
                    'primaryKey' => false,
                    'auto_increment' => false,
                    'required' => false,
                    'references' => [
                        'fk_othertable_role' => ['OtherTable', 'id']
                    ]
                ],
                'role2_id' => [
                    'primaryKey' => false,
                    'auto_increment' => false,
                    'required' => false,
                    'references' => [
                        'fk_anothertable_role' => ['AntoherTable', 'id']
                    ]
                ],
                'extra_column_id' => [
                    'primaryKey' => false,
                    'auto_increment' => false,
                    'required' => false,
                    'references' => [
                        'fk_anothertable_role' => ['AntoherTable', 'column_id']
                    ]
                ],
            ],
            'MyTable_today' => 'CREATE VIEW `MyTable_today` AS
  SELECT
    `schema`.`MyTable`.`name`   AS `name`,
    `schema`.`MyTable`.`birthdate` AS `birthdate`
  FROM `teach`.`MyTable`;'
        ]);

        $schemaDescription = $schema->describeTables(new Table());

        $this->assertEquals('MyTable', $schemaDescription['MyTable_today']['entityTypeIdentifier']);
    }
}
