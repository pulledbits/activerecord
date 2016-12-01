<?php
namespace ActiveRecord;

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
                    'id' => new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}},
                    'name' => new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}},
                    'height' => new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}}
                ];
            }
        });
        $classDescription = $table->describe('\\Database\\Table');
        $this->assertEquals($classDescription['properties'][0], 'id');
        $this->assertEquals($classDescription['properties'][1], 'name');
        $this->assertEquals($classDescription['properties'][2], 'height');
    }
    
    public function testDescribe_When_ForeignKeysAvailable_Expect_ArrayWithClassForeignKeys()
    {
        $table = new Table(new class() extends \Doctrine\DBAL\Schema\Table {

            public function __construct()
            {}

            public function getName()
            {
                return 'MyTable2';
            }
            public function getForeignKeys()
            {
                return [
                    'fk_othertable_role' => new class extends \Doctrine\DBAL\Schema\ForeignKeyConstraint {
                        public function __construct(){}
                        public function getLocalColumns() {
                            return ['role_id'];
                        }
                    },
                    'fk_anothertable_role' => new class extends \Doctrine\DBAL\Schema\ForeignKeyConstraint {
                        public function __construct(){}
                        public function getLocalColumns() {
                            return ['role2_id', 'extra_column_id'];
                        }
                    }
                ];
            }
        });
        $classDescription = $table->describe('\\Database\\Table');
        $this->assertEquals($classDescription['methods']['fetchByFkOthertableRole']['parameters'][0], 'role_id');
        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['parameters'][0], 'role2_id');
        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['parameters'][1], 'extra_column_id');
    }
}