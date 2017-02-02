<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:36
 */

namespace ActiveRecord\SQL\Source;

class SchemaTest extends \PHPUnit_Framework_TestCase
{

    public function testDescribe_When_Default_Expect_ArrayWithClasses()
    {
        $schema = \ActiveRecord\Test\createMockSchema([
            'MyTable' => [
                'extra_column_id' => [
                    'primaryKey' => false,
                    'references' => [
                        'fk_anothertable_role' => ['AntoherTable', 'column_id']
                    ]
                ]
            ],
            'AnotherTable' => []
        ]);

        $schemaDescription = $schema->describe(new Table('\\Database\\Record'));

        $this->assertEquals([
            'FkAnothertableRole' => [
                'table' => 'AntoherTable',
                'where' => [
                    'column_id' => 'extra_column_id'
                ],
            ]
        ], $schemaDescription['MyTable']['references']);
//        $this->assertEquals([
//            'FkAnothertableRole' => [
//                'table' => 'AntoherTable',
//                'where' => [
//                    'column_id' => 'extra_column_id'
//                ],
//            ]
//        ], $schemaDescription['AnotherTable']['references']);
    }


    public function testDescribe_When_ViewAvailable_Expect_ArrayWithReadableClasses()
    {
        $schema = \ActiveRecord\Test\createMockSchema([
            'MyView' => 'CREATE VIEW `MyView` AS
  SELECT
    `schema`.`MyTable`.`name`   AS `name`,
    `schema`.`MyTable`.`birthdate` AS `birthdate`
  FROM `teach`.`thema`;'
        ]);

        $schemaDescription = $schema->describe(new Table('\\Database\\Record'));
        $this->assertArrayHasKey('MyView', $schemaDescription);
    }
}
