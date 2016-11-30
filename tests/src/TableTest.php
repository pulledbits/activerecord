<?php
namespace ActiveRecord;


class TableTest extends \PHPUnit_Framework_TestCase
{

    public function testMakeClass_When_DefaultState_Expect_ArrayWithClassIdentifier()
    {
        $table = new Table('MyTable');
        $classDescription = $table->describe('\\Database\\Table');
        $this->assertEquals($classDescription['identifier'], '\\Database\\Table\\MyTable');
    }

    public function testMakeClass_When_DifferingTableName_Expect_ArrayWithClassIdentifierAndDifferentClassName()
    {
        $table = new Table('MyTable2');
        $classDescription = $table->describe('\\Database\\Table');
        $this->assertEquals($classDescription['identifier'], '\\Database\\Table\\MyTable2');
    }
}