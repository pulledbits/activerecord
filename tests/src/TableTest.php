<?php
namespace ActiveRecord;


class TableTest extends \PHPUnit_Framework_TestCase
{

    public function testMakeClass_When_DefaultState_Expect_ArrayWithClassIdentifier()
    {
        $table = new Table('MyTable');
        $classDescription = $table->describe();
        $this->assertEquals($classDescription['identifier'], 'MyTable');
    }
}