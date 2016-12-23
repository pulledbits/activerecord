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
        $schema = createMockSchema([
            'MyTable' => []
        ]);

        $schemaDescription = $schema->describe(new Table('\\Database\\Record'));


        $this->assertCount(1, $schemaDescription['recordClasses']);
        $this->assertEquals('\\Database\\Record\\MyTable', $schemaDescription['recordClasses']['MyTable']['identifier']);
    }

}
