<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:36
 */

namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator\Record;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator\WrappedEntity;

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

        $this->assertEquals(new Record(['identifier' => [], 'requiredAttributeIdentifiers' => [], 'references' => [
            'FkAnothertableRole' => [
                'table' => 'AnotherTable',
                'where' => [
                    'column_id' => 'extra_column_id'
                ],
            ]
        ]]), $schema->describeTable('MyTable'));
        $this->assertEquals(new Record(['identifier' => [], 'requiredAttributeIdentifiers' => [], 'references' => [
            'FkAnothertableRole' => [
                'table' => 'MyTable',
                'where' => [
                    'extra_column_id' => 'column_id'
                ],
            ]
        ]]), $schema->describeTable('AnotherTable'));
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

        $tableDescription = $schema->describeTable('MyView');

        $this->assertEquals(new Record(['identifier' => [], 'requiredAttributeIdentifiers' => [], 'references' => []]), $tableDescription);
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

        $tableDescription = $schema->describeTable('MyTable_today');

        $this->assertEquals(new WrappedEntity('MyTable'), $tableDescription);
    }
}
