<?php
namespace ActiveRecord;

use Doctrine\DBAL\Schema\Column;

class TableTest extends \PHPUnit_Framework_TestCase
{

    public function testDescribe_When_DefaultState_Expect_ArrayWithClassIdentifier()
    {
        $table = new Table(new class() extends \Doctrine\DBAL\Schema\Table {

            public function __construct()
            {}

            public function getName()
            {
                return 'MyTable';
            }
        });
        $classDescription = $table->describe('\\Database\\Table');
        $this->assertEquals($classDescription['identifier'], '\\Database\\Table\\MyTable');
    }

    public function testDescribe_When_DifferingTableName_Expect_ArrayWithClassIdentifierAndDifferentClassName()
    {
        $table = new Table(new class() extends \Doctrine\DBAL\Schema\Table {

            public function __construct()
            {}

            public function getName()
            {
                return 'MyTable2';
            }
        });
        $classDescription = $table->describe('\\Database\\Table');
        $this->assertEquals($classDescription['identifier'], '\\Database\\Table\\MyTable2');
    }

    public function testDescribe_When_ColumnsAvailable_Expect_ArrayWithClassColumns()
    {
        $table = new Table(new class() extends \Doctrine\DBAL\Schema\Table {

            public function __construct()
            {}

            public function getName()
            {
                return 'MyTable2';
            }
            public function getColumns()
            {
                return [
                    'id' => new class extends Column {public function __construct(){}},
                    'name' => new class extends Column {public function __construct(){}},
                    'height' => new class extends Column {public function __construct(){}}
                ];
            }
        });
        $classDescription = $table->describe('\\Database\\Table');
        $this->assertEquals($classDescription['properties'][0], 'id');
        $this->assertEquals($classDescription['properties'][1], 'name');
        $this->assertEquals($classDescription['properties'][2], 'height');
    }
}