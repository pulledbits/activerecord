<?php
namespace ActiveRecord\Source;

class TableTest extends \PHPUnit_Framework_TestCase
{

    public function testDescribe_When_DefaultState_Expect_ArrayWithClassIdentifier()
    {
        $dbalTable = new class() extends \Doctrine\DBAL\Schema\Table {
            public function __construct()
            {}

            public function getName()
            {
                return 'MyTable';
            }
        };

        $table = new Table('\\Database\\Record');
        $classDescription = $table->describe($dbalTable);
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable');

        $this->assertEquals($classDescription['properties']['schema'], '\ActiveRecord\Schema');

        $this->assertEquals($classDescription['methods']['__construct']['parameters']['schema'], '\ActiveRecord\Schema');
        $this->assertEquals($classDescription['methods']['__construct']['body'][0], '$this->schema = $schema;');
        $this->assertEquals('$this->primaryKey = [];', $classDescription['methods']['__construct']['body'][1]);

    }
    
    public function testDescribe_When_DifferingTableName_Expect_ArrayWithClassIdentifierAndDifferentClassName()
    {
        $dbalTable = new class() extends \Doctrine\DBAL\Schema\Table {

            public function __construct()
            {}

            public function getName()
            {
                return 'MyTable2';
            }
        };

        $table = new Table('\\Database\\Record');
        $classDescription = $table->describe($dbalTable);
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable2');
    }

    public function testDescribe_When_DifferingTableName_Expect_FetchAllMethod()
    {
        $dbalTable = new class() extends \Doctrine\DBAL\Schema\Table {

            public function __construct()
            {}

            public function getName()
            {
                return 'MyTable2';
            }
            public function getColumns()
            {
                return [
                    'id' => new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}}
                ];
            }
        };

        $table = new Table('\\Database\\Record');
        $classDescription = $table->describe($dbalTable);
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable2');
        $this->assertEquals($classDescription['methods']['fetchAll']['body'][0], 'return $this->schema->select("MyTable2", [\'_id\' => \'id\'], [');
        $this->assertEquals($classDescription['methods']['fetchAll']['body'][1], '');
        $this->assertEquals($classDescription['methods']['fetchAll']['body'][2], ']);');
    }

    public function testDescribe_When_Default_Expect___setMethod()
    {
        $dbalTable = new class() extends \Doctrine\DBAL\Schema\Table {

            public function __construct()
            {}

            public function getName()
            {
                return 'MyTable';
            }

            public function hasPrimaryKey() {
                return true;
            }
            public function getPrimaryKeyColumns() {
                return ['name', 'birthdate'];
            }
            public function getColumns()
            {
                return [
                    'name' => new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}},
                    'birthdate' => new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}},
                    'address' => new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}}
                ];
            }
        };

        $table = new Table('\\Database\\Record');
        $classDescription = $table->describe($dbalTable);
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable');

        $this->assertEquals($classDescription['methods']['__set']['parameters']['property'], 'string');
        $this->assertEquals($classDescription['methods']['__set']['parameters']['value'], 'string');

        $this->assertEquals('if (property_exists($this, $property)) {', $classDescription['methods']['__set']['body'][0]);
        $this->assertEquals('$this->$property = $value;', $classDescription['methods']['__set']['body'][1]);
        $this->assertEquals('$this->schema->update("MyTable", [' . join(',' . PHP_EOL, ['\'name\' => $this->_name', '\'birthdate\' => $this->_birthdate', '\'address\' => $this->_address']) . '], [' . join(',' . PHP_EOL, ['\'name\' => $this->_name', '\'birthdate\' => $this->_birthdate']) . ']);', $classDescription['methods']['__set']['body'][2]);
        $this->assertEquals('}', $classDescription['methods']['__set']['body'][3]);
    }

    public function testDescribe_When_ColumnsAvailable_Expect_ArrayWithClassColumns()
    {
        $dbalTable = new class() extends \Doctrine\DBAL\Schema\Table {

            public function __construct()
            {}

            public function getName()
            {
                return 'MyTable2';
            }
            public function hasPrimaryKey() {
                return true;
            }
            public function getPrimaryKeyColumns() {
                return ['id'];
            }
            public function getColumns()
            {
                return [
                    'id' => new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}},
                    'name' => new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}},
                    'height' => new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}}
                ];
            }
        };

        $table = new Table('\\Database\\Record');
        $classDescription = $table->describe($dbalTable);

        $this->assertEquals('array', $classDescription['properties']['primaryKey']);
        $this->assertEquals('$this->primaryKey = [\'_id\'];', $classDescription['methods']['__construct']['body'][1]);

        $this->assertEquals('string', $classDescription['properties']['_id']);
        $this->assertEquals('string', $classDescription['properties']['_name']);
        $this->assertEquals('string', $classDescription['properties']['_height']);
    }
    
    public function testDescribe_When_ForeignKeysAvailable_Expect_ArrayWithClassForeignKeys()
    {
        $dbalTable = new class() extends \Doctrine\DBAL\Schema\Table {

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
        };

        $table = new Table('\\Database');
        $classDescription = $table->describe($dbalTable);
        $this->assertEquals($classDescription['methods']['fetchByFkOthertableRole']['parameters'], []);

        $this->assertEquals('return $this->schema->select("OtherTable", [\'_id\' => \'id\'], [', $classDescription['methods']['fetchByFkOthertableRole']['body'][0]);
        $this->assertEquals(join(',' . PHP_EOL, ['\'id\' => $this->_role_id']), $classDescription['methods']['fetchByFkOthertableRole']['body'][1]);
        $this->assertEquals(']);', $classDescription['methods']['fetchByFkOthertableRole']['body'][2]);

        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['parameters'], []);

        $this->assertEquals('return $this->schema->select("AntoherTable", [\'_id\' => \'id\', \'_column_id\' => \'column_id\'], [', $classDescription['methods']['fetchByFkAnothertableRole']['body'][0]);
        $this->assertEquals(join(',' . PHP_EOL, ['\'id\' => $this->_role2_id', '\'column_id\' => $this->_extra_column_id']), $classDescription['methods']['fetchByFkAnothertableRole']['body'][1]);
        $this->assertEquals(']);', $classDescription['methods']['fetchByFkAnothertableRole']['body'][2]);
    }
}