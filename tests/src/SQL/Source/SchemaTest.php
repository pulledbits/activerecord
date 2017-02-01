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
            'MyTable' => []
        ]);

        $called = false;
        $schema->describe(new Table('\\Database\\Record'), function(string $tableName, array $tableDescription) use (&$called) {
            $this->assertEquals('MyTable', $tableName);
            $called = true;
        });
        $this->assertTrue($called);
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

        $called = false;
        $schema->describe(new Table('\\Database\\Record'), function(string $tableName, array $tableDescription) use (&$called) {
            $this->assertEquals('MyView', $tableName);
            $called = true;
        });
        $this->assertTrue($called);
    }
}