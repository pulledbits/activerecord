<?php
namespace ActiveRecord\Source;

function createMockTable(string $tableIdentifier, array $columns) {
    return new class($tableIdentifier, $columns) extends \Doctrine\DBAL\Schema\Table {

        private $tableIdentifier;
        private $primaryKey;
        private $foreignKeys;
        private $columns;

        public function __construct(string $tableIdentifier, array $columns)
        {
            $this->tableIdentifier = $tableIdentifier;
            $this->columns = [];
            $this->primaryKey = [];
            $foreignKeys = [];
            foreach ($columns as $columnIdentifier => $column) {
                $this->columns[$columnIdentifier] = new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}};
                if ($column['primaryKey']) {
                    $this->primaryKey[] = $columnIdentifier;
                }
                if (array_key_exists('references', $column)) {
                    foreach ($column['references'] as $foreignKeyIdentifier => $foreignKey) {
                        if (array_key_exists($foreignKeyIdentifier, $foreignKeys) === false) {
                            $foreignKeys[$foreignKeyIdentifier] = [
                                'table' => $foreignKey[0],
                                'columns' => [],
                                'foreignColumns' => []
                            ];
                        }

                        $foreignKeys[$foreignKeyIdentifier]['columns'][] = $columnIdentifier;
                        $foreignKeys[$foreignKeyIdentifier]['foreignColumns'][] = $foreignKey[1];
                    }
                }
            }

            $this->foreignKeys = [];
            foreach ($foreignKeys as $foreignKeyIdentifier => $foreignKey) {
                $this->foreignKeys[$foreignKeyIdentifier] = new \Doctrine\DBAL\Schema\ForeignKeyConstraint($foreignKey['columns'], $foreignKey['table'], $foreignKey['foreignColumns'], $foreignKeyIdentifier);
            }

        }

        public function getName()
        {
            return $this->tableIdentifier;
        }
        public function getColumns()
        {
            return $this->columns;
        }
        public function hasPrimaryKey() {
            return count($this->primaryKey) > 0;
        }
        public function getPrimaryKeyColumns() {
            return $this->primaryKey;
        }
        public function getForeignKeys()
        {
            return $this->foreignKeys;
        }
    };
}

class TableTest extends \PHPUnit_Framework_TestCase
{

    public function testDescribe_When_DefaultState_Expect_ArrayWithClassIdentifier()
    {
        $dbalTable = createMockTable('MyTable', [
            'name' => [
                'primaryKey' => true
            ],
            'birthdate' => [
                'primaryKey' => true
            ]
        ]);

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


        $this->assertCount(0, $classDescription['methods']['primaryKey']['parameters']);
        $this->assertEquals('return [\'name\' => $this->__get(\'name\'), \'birthdate\' => $this->__get(\'birthdate\')];', $classDescription['methods']['primaryKey']['body'][0]);

    }
    
    public function testDescribe_When_DifferingTableName_Expect_ArrayWithClassIdentifierAndDifferentClassName()
    {
        $dbalTable = createMockTable('MyTable2', []);

        $table = new Table('\\Database\\Record');
        $classDescription = $table->describe($dbalTable);
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable2');
    }

    public function testDescribe_When_Default_Expect___setMethod()
    {
        $dbalTable = createMockTable('MyTable', [
            'name' => [
                'primaryKey' => true
            ],
            'birthdate' => [
                'primaryKey' => true
            ],
            'address' => [
                'primaryKey' => false
            ]
        ]);

        $table = new Table('\\Database\\Record');
        $classDescription = $table->describe($dbalTable);
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable');

        $this->assertEquals($classDescription['methods']['__set']['parameters']['property'], 'string');
        $this->assertEquals($classDescription['methods']['__set']['parameters']['value'], 'string');
        $this->assertFalse($classDescription['methods']['__set']['static']);

        $this->assertEquals('if (property_exists($this, $this->table->transformColumnToProperty($property))) {', $classDescription['methods']['__set']['body'][0]);
        $this->assertEquals('$this->{$this->table->transformColumnToProperty($property)} = $value;', $classDescription['methods']['__set']['body'][1]);
        $this->assertEquals('$this->table->update("MyTable", [$property => $this->__get($property)], $this->primaryKey());', $classDescription['methods']['__set']['body'][2]);
        $this->assertEquals('}', $classDescription['methods']['__set']['body'][3]);
    }

    public function testDescribe_When_Default_Expect___getMethod()
    {
        $dbalTable = createMockTable('MyTable', [
            'name' => [
                'primaryKey' => true
            ],
            'birthdate' => [
                'primaryKey' => true
            ],
            'address' => [
                'primaryKey' => false
            ]
        ]);

        $table = new Table('\\Database\\Record');
        $classDescription = $table->describe($dbalTable);
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable');

        $this->assertEquals($classDescription['methods']['__get']['parameters']['property'], 'string');
        $this->assertFalse($classDescription['methods']['__get']['static']);

        $this->assertEquals('return $this->{$this->table->transformColumnToProperty($property)};', $classDescription['methods']['__get']['body'][0]);
    }

    public function testDescribe_When_Default_Expect_DeleteMethod()
    {
        $dbalTable = createMockTable('MyTable', [
            'name' => [
                'primaryKey' => true
            ],
            'birthdate' => [
                'primaryKey' => true
            ],
            'address' => [
                'primaryKey' => false
            ]
        ]);

        $table = new Table('\\Database\\Record');
        $classDescription = $table->describe($dbalTable);

        $this->assertEquals($classDescription['methods']['delete']['parameters'], []);
        $this->assertFalse($classDescription['methods']['delete']['static']);
        $this->assertEquals('return $this->table->delete("MyTable", $this->primaryKey());', $classDescription['methods']['delete']['body'][0]);


    }

    public function testDescribe_When_ColumnsAvailable_Expect_ArrayWithClassColumns()
    {
        $dbalTable = createMockTable('MyTable2', [
            'id' => [
                'primaryKey' => true
            ],
            'name' => [
                'primaryKey' => false
            ],
            'height' => [
                'primaryKey' => false
            ]
        ]);

        $table = new Table('\\Database\\Record');
        $classDescription = $table->describe($dbalTable);

        $this->assertEquals(['string', ['static' => false, 'value' => null]], $classDescription['properties']['_id']);
        $this->assertEquals(['string', ['static' => false, 'value' => null]], $classDescription['properties']['_name']);
        $this->assertEquals(['string', ['static' => false, 'value' => null]], $classDescription['properties']['_height']);
    }
    
    public function testDescribe_When_ForeignKeysAvailable_Expect_ArrayWithClassForeignKeys()
    {
        $dbalTable = createMockTable('MyTable2', [
            'role_id' => [
                'primaryKey' => true,
                'references' => [
                    'fk_othertable_role' => ['OtherTable', 'id']
                ]
            ],
            'role2_id' => [
                'primaryKey' => false,
                'references' => [
                    'fk_anothertable_role' => ['AntoherTable', 'id']
                ]
            ],
            'extra_column_id' => [
                'primaryKey' => false,
                'references' => [
                    'fk_anothertable_role' => ['AntoherTable', 'column_id']
                ]
            ],
        ]);

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