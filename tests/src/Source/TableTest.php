<?php
namespace ActiveRecord\Source;

class TableTest extends \PHPUnit_Framework_TestCase
{

    private $table;

    protected function setUp()
    {
        $this->table = new Table('\\Database\\Record');
    }

    public function testDescribe_When_DefaultState_Expect_ClassDescription()
    {
        $classDescription = $this->table->describe(\ActiveRecord\Test\createMockTable('MyTable', [
            'name' => [
                'primaryKey' => true
            ],
            'birthdate' => [
                'primaryKey' => true
            ],
            'address' => [
                'primaryKey' => false
            ]
        ]));
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable');
        $this->assertEquals($classDescription['interfaces'][0], '\\ActiveRecord\\WritableRecord');

        $this->assertEquals(['\ActiveRecord\Table', ['static' => false, 'value' => null]], $classDescription['properties']['table']);
        $this->assertEquals(['array', ['static' => false, 'value' => null]], $classDescription['properties']['values']);

        $this->assertEquals('\ActiveRecord\Table', $classDescription['methods']['__construct']['parameters']['table']);
        $this->assertEquals('array', $classDescription['methods']['__construct']['parameters']['values']);
        $this->assertFalse($classDescription['methods']['__construct']['static']);
        $this->assertEquals('$this->table = $table;', $classDescription['methods']['__construct']['body'][0]);
        $this->assertEquals('$this->values = $values;', $classDescription['methods']['__construct']['body'][1]);


        $this->assertCount(0, $classDescription['methods']['primaryKey']['parameters']);
        $this->assertEquals('return [\'name\' => $this->__get(\'name\'), \'birthdate\' => $this->__get(\'birthdate\')];', $classDescription['methods']['primaryKey']['body'][0]);

        $this->assertEquals($classDescription['methods']['__set']['parameters']['property'], 'string');
        $this->assertEquals($classDescription['methods']['__set']['parameters']['value'], 'string');
        $this->assertFalse($classDescription['methods']['__set']['static']);
        $this->assertEquals('if (count($this->table->update([$property => $this->__get($property)], $this->primaryKey())) > 0) {', $classDescription['methods']['__set']['body'][0]);
        $this->assertEquals('$this->values[$property] = $value;', $classDescription['methods']['__set']['body'][1]);
        $this->assertEquals('}', $classDescription['methods']['__set']['body'][2]);

        $this->assertEquals($classDescription['methods']['__get']['parameters']['property'], 'string');
        $this->assertFalse($classDescription['methods']['__get']['static']);
        $this->assertEquals('return $this->values[$property];', $classDescription['methods']['__get']['body'][0]);

        $this->assertEquals($classDescription['methods']['delete']['parameters'], []);
        $this->assertFalse($classDescription['methods']['delete']['static']);
        $this->assertEquals('return $this->table->delete($this->primaryKey());', $classDescription['methods']['delete']['body'][0]);
    }
    
    public function testDescribe_When_DifferingTableName_Expect_ArrayWithClassIdentifierAndDifferentClassName()
    {
        $dbalTable = \ActiveRecord\Test\createMockTable('MyTable2', []);
        $classDescription = $this->table->describe($dbalTable);
        $this->assertEquals($classDescription['identifier'], '\\Database\\Record\\MyTable2');
    }
    
    public function testDescribe_When_ForeignKeysAvailable_Expect_ArrayWithClassForeignKeys()
    {
        $dbalTable = \ActiveRecord\Test\createMockTable('MyTable2', [
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

        $this->assertEquals('return $this->table->selectFrom("OtherTable", [\'id\'], [', $classDescription['methods']['fetchByFkOthertableRole']['body'][0]);
        $this->assertEquals(join(',' . PHP_EOL, ['\'id\' => $this->__get(\'role_id\')']), $classDescription['methods']['fetchByFkOthertableRole']['body'][1]);
        $this->assertEquals(']);', $classDescription['methods']['fetchByFkOthertableRole']['body'][2]);

        $this->assertEquals($classDescription['methods']['fetchByFkAnothertableRole']['parameters'], []);
        $this->assertFalse($classDescription['methods']['fetchByFkAnothertableRole']['static']);

        $this->assertEquals('return $this->table->selectFrom("AntoherTable", [\'id\', \'column_id\'], [', $classDescription['methods']['fetchByFkAnothertableRole']['body'][0]);
        $this->assertEquals(join(',' . PHP_EOL, ['\'id\' => $this->__get(\'role2_id\')', '\'column_id\' => $this->__get(\'extra_column_id\')']), $classDescription['methods']['fetchByFkAnothertableRole']['body'][1]);
        $this->assertEquals(']);', $classDescription['methods']['fetchByFkAnothertableRole']['body'][2]);
    }
}