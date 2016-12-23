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
            public function getColumns()
            {
                return [
                    'name' => new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}},
                    'birthdate' => new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}}
                ];
            }
            public function hasPrimaryKey() {
                return true;
            }
            public function getPrimaryKeyColumns() {
                return ['name', 'birthdate'];
            }
        };

        $table = new Table('\\Database\\Record');
        $classDescription = $table->describe($dbalTable);
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable');

        $this->assertEquals(['\ActiveRecord\Table', ['static' => false, 'value' => null]], $classDescription['properties']['table']);

        $this->assertEquals('\ActiveRecord\Table', $classDescription['methods']['__construct']['parameters']['table']);
        $this->assertEquals('array', $classDescription['methods']['__construct']['parameters']['values']);
        $this->assertFalse($classDescription['methods']['__construct']['static']);
        $this->assertEquals('$this->table = $table;', $classDescription['methods']['__construct']['body'][0]);
        $this->assertEquals('foreach ($values as $columnIdentifier => $value) {', $classDescription['methods']['__construct']['body'][1]);
        $this->assertEquals('    $this->{$this->table->transformColumnToProperty($columnIdentifier)} = $value;', $classDescription['methods']['__construct']['body'][2]);
        $this->assertEquals('}', $classDescription['methods']['__construct']['body'][3]);


        $this->assertEquals('return [\'name\' => $this->__get(\'name\'), \'birthdate\' => $this->__get(\'birthdate\')];', $classDescription['methods']['primaryKey']['body'][0]);

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
        $this->assertFalse($classDescription['methods']['fetchAll']['static']);
        $this->assertEquals('return $this->table->select("MyTable2", [\'id\'], [', $classDescription['methods']['fetchAll']['body'][0]);
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
        $this->assertFalse($classDescription['methods']['__set']['static']);

        $this->assertEquals('if (property_exists($this, $this->table->transformColumnToProperty($property))) {', $classDescription['methods']['__set']['body'][0]);
        $this->assertEquals('$this->{$this->table->transformColumnToProperty($property)} = $value;', $classDescription['methods']['__set']['body'][1]);
        $this->assertEquals('$this->table->update("MyTable", [$property => $this->__get($property)], [' . join(',' . PHP_EOL, ['\'name\' => $this->__get(\'name\')', '\'birthdate\' => $this->__get(\'birthdate\')']) . ']);', $classDescription['methods']['__set']['body'][2]);
        $this->assertEquals('}', $classDescription['methods']['__set']['body'][3]);
    }

    public function testDescribe_When_Default_Expect___getMethod()
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

        $this->assertEquals($classDescription['methods']['__get']['parameters']['property'], 'string');
        $this->assertFalse($classDescription['methods']['__get']['static']);

        $this->assertEquals('return $this->{$this->table->transformColumnToProperty($property)};', $classDescription['methods']['__get']['body'][0]);
    }

    public function testDescribe_When_Default_Expect_DeleteMethod()
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

        $this->assertEquals($classDescription['methods']['delete']['parameters'], []);
        $this->assertFalse($classDescription['methods']['delete']['static']);
        $this->assertEquals('return $this->table->delete("MyTable", [' . join(',' . PHP_EOL, ['\'name\' => $this->__get(\'name\')', '\'birthdate\' => $this->__get(\'birthdate\')']) . ']);', $classDescription['methods']['delete']['body'][0]);


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

        $this->assertEquals(['string', ['static' => false, 'value' => null]], $classDescription['properties']['_id']);
        $this->assertEquals(['string', ['static' => false, 'value' => null]], $classDescription['properties']['_name']);
        $this->assertEquals(['string', ['static' => false, 'value' => null]], $classDescription['properties']['_height']);
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
        $this->assertFalse($classDescription['methods']['fetchByFkOthertableRole']['static']);

        $this->assertEquals('return $this->table->select("OtherTable", [\'id\'], [', $classDescription['methods']['fetchByFkOthertableRole']['body'][0]);
        $this->assertEquals(join(',' . PHP_EOL, ['\'id\' => $this->__get(\'role_id\')']), $classDescription['methods']['fetchByFkOthertableRole']['body'][1]);
        $this->assertEquals(']);', $classDescription['methods']['fetchByFkOthertableRole']['body'][2]);

        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['parameters'], []);
        $this->assertFalse($classDescription['methods']['fetchByFkAnothertableRole']['static']);

        $this->assertEquals('return $this->table->select("AntoherTable", [\'id\', \'column_id\'], [', $classDescription['methods']['fetchByFkAnothertableRole']['body'][0]);
        $this->assertEquals(join(',' . PHP_EOL, ['\'id\' => $this->__get(\'role2_id\')', '\'column_id\' => $this->__get(\'extra_column_id\')']), $classDescription['methods']['fetchByFkAnothertableRole']['body'][1]);
        $this->assertEquals(']);', $classDescription['methods']['fetchByFkAnothertableRole']['body'][2]);
    }
}