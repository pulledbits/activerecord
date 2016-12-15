<?php
namespace ActiveRecord\Source;

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
        $classDescription = $table->describe('\\Database\\Record');
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable');
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
        $classDescription = $table->describe('\\Database\\Record');
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable2');
    }

    public function testDescribe_When_DifferingTableName_Expect_FetchAllMethod()
    {
        $table = new Table(new class() extends \Doctrine\DBAL\Schema\Table {

            public function __construct()
            {}

            public function getName()
            {
                return 'MyTable2';
            }
        });
        $classDescription = $table->describe('\\Database\\Record');
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable2');
        $this->assertEquals($classDescription['methods']['fetchAll']['body'][0], 'return $this->schema->select("MyTable2", [');
        $this->assertEquals($classDescription['methods']['fetchAll']['body'][1], '');
        $this->assertEquals($classDescription['methods']['fetchAll']['body'][2], ']);');
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
        $classDescription = $table->describe('\\Database\\Record');
        $this->assertEquals($classDescription['properties']['columns'][0], 'id');
        $this->assertEquals($classDescription['properties']['columns'][1], 'name');
        $this->assertEquals($classDescription['properties']['columns'][2], 'height');
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
        $classDescription = $table->describe('\\Database');
        $this->assertEquals($classDescription['methods']['fetchByFkOthertableRole']['parameters']['role_id'], 'string');

        $this->assertEquals($classDescription['methods']['fetchByFkOthertableRole']['body'][0], 'return $this->schema->select("OtherTable", [');
        $this->assertEquals($classDescription['methods']['fetchByFkOthertableRole']['body'][1], join(',' . PHP_EOL, ['\'id\' => $this->role_id']));
        $this->assertEquals($classDescription['methods']['fetchByFkOthertableRole']['body'][2], ']);');

        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['parameters']['role2_id'], 'string');
        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['parameters']['extra_column_id'], 'string');

        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['body'][0], 'return $this->schema->select("AntoherTable", [');
        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['body'][1], join(',' . PHP_EOL, ['\'id\' => $this->role2_id', '\'column_id\' => $this->extra_column_id']));
        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['body'][2], ']);');
    }
}