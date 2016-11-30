<?php
namespace ActiveRecord;


class TableTest extends \PHPUnit_Framework_TestCase
{

    public function testMakeClass_When_DefaultState_Expect_ArrayWithClassIdentifier()
    {
        $table = new Table(new class extends \Doctrine\DBAL\Schema\Table {
            public function __construct() {
                
            }
            public function getName() {
                return 'MyTable';
            }
        });
        $classDescription = $table->describe('\\Database\\Table');
        $this->assertEquals($classDescription['identifier'], '\\Database\\Table\\MyTable');
    }

    public function testMakeClass_When_DifferingTableName_Expect_ArrayWithClassIdentifierAndDifferentClassName()
    {
        $table = new Table(new class extends \Doctrine\DBAL\Schema\Table {
            public function __construct() {
                
            }
            public function getName() {
                return 'MyTable2';
            }
            
        });
        $classDescription = $table->describe('\\Database\\Table');
        $this->assertEquals($classDescription['identifier'], '\\Database\\Table\\MyTable2');
    }
}