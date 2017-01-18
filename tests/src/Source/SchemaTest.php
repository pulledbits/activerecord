<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:36
 */

namespace ActiveRecord\Source;

class SchemaTest extends \PHPUnit_Framework_TestCase
{

    public function testDescribe_When_Default_Expect_ArrayWithClasses()
    {
        $schema = \ActiveRecord\Test\createMockSchema([
            'MyTable' => []
        ]);

        $called = false;
        $schema->describe(new Table('\\Database\\Record'), function(string $tableName, array $tableDescription) use (&$called) {
            $this->assertEquals('\\Database\\Record\\MyTable', $tableDescription['identifier']);
            $this->assertEquals('MyTable', $tableName);
            $called = true;
        });
        $this->assertTrue($called);
    }

}
