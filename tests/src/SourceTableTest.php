<?php
namespace ActiveRecord;

class SourceTableTest extends \PHPUnit_Framework_TestCase
{

    public function testDescribe_When_DefaultState_Expect_ArrayWithClassIdentifier()
    {
        $table = new SourceTable(new class() extends \Doctrine\DBAL\Schema\Table {

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
        $table = new SourceTable(new class() extends \Doctrine\DBAL\Schema\Table {

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

    public function testDescribe_When_DifferingTableName_Expect_FetchAllMethod()
    {
        $table = new SourceTable(new class() extends \Doctrine\DBAL\Schema\Table {

            public function __construct()
            {}

            public function getName()
            {
                return 'MyTable2';
            }
        });
        $classDescription = $table->describe('\\Database\\Table');
        $this->assertEquals($classDescription['identifier'], '\\Database\\Table\\MyTable2');
        $this->assertEquals($classDescription['methods']['fetchAll']['query'][0], 'SELECT');
        $this->assertEquals($classDescription['methods']['fetchAll']['query'][1]['fields'], '*');
        $this->assertEquals($classDescription['methods']['fetchAll']['query'][1]['from'], 'MyTable2');
    }

    public function testDescribe_When_ColumnsAvailable_Expect_ArrayWithClassColumns()
    {
        $table = new SourceTable(new class() extends \Doctrine\DBAL\Schema\Table {

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
        $this->assertEquals($classDescription['properties']['columns'][0], 'id');
        $this->assertEquals($classDescription['properties']['columns'][1], 'name');
        $this->assertEquals($classDescription['properties']['columns'][2], 'height');
    }
    
    public function testDescribe_When_ForeignKeysAvailable_Expect_ArrayWithClassForeignKeys()
    {
        $table = new SourceTable(new class() extends \Doctrine\DBAL\Schema\Table {

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
                        public function getForeignTableName()
                        {
                            return "OtherTable";
                        }
                        public function getForeignColumns() {
                            return ['id'];
                        }
                        public function getLocalColumns()
                        {
                            return ['role_id'];
                        }
                    },
                    'fk_anothertable_role' => new class extends \Doctrine\DBAL\Schema\ForeignKeyConstraint
                    {
                        public function __construct()
                        {
                        }

                        public function getForeignTableName()
                        {
                            return "AntoherTable";
                        }

                        public function getForeignColumns()
                        {
                            return ['id', 'column_id'];
                        }

                        public function getLocalColumns()
                        {
                            return ['role2_id', 'extra_column_id'];
                        }
                    }
                ];
            }
        });
        $classDescription = $table->describe('\\Database\\Table');
        $this->assertEquals($classDescription['methods']['fetchByFkOthertableRole']['parameters'][0], 'role_id');
        
        $this->assertEquals($classDescription['methods']['fetchByFkOthertableRole']['query'][0], 'SELECT');
        $this->assertEquals($classDescription['methods']['fetchByFkOthertableRole']['query'][1]['fields'], '*');
        $this->assertEquals($classDescription['methods']['fetchByFkOthertableRole']['query'][1]['from'], 'OtherTable');
        $this->assertEquals($classDescription['methods']['fetchByFkOthertableRole']['query'][1]['where'], 'id = :role_id');
        
        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['parameters'][0], 'role2_id');
        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['parameters'][1], 'extra_column_id');
        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['query'][1]['fields'], '*');
        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['query'][1]['from'], 'AntoherTable');
        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['query'][1]['where'], 'id = :role2_id AND column_id = :extra_column_id');
    }
}