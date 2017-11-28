<?php

namespace pulledbits\ActiveRecord\SQL\Meta;


use pulledbits\ActiveRecord\SQL\Connection;
use function pulledbits\ActiveRecord\Test\createMockPDOMultiple;
use function pulledbits\ActiveRecord\Test\createMockSchemaManager;

class SchemaFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $connection;
    private $schema;

    protected function setUp()
    {
        $this->connection = new Connection(createMockPDOMultiple([]));
        $this->schema = $this->connection->schema();
    }

    public function testmakeFromSchemaManager_When_Default_Expect_ArrayWithRecordConfigurators()
    {
        $sourceSchema = SchemaFactory::makeFromSchemaManager($this->connection, createMockSchemaManager([
            'MyTable' => [
                'extra_column_id' => [
                    'primaryKey' => false,
                    'auto_increment' => false,
                    'required' => false,
                    'references' => [
                        'fk_anothertable_role' => ['AnotherTable', 'column_id']
                    ]
                ]
            ],
            'AnotherTable' => []
        ]));

        $this->assertEquals(new Record($this->schema->makeRecordType('MyTable'), new TableDescription([], [], [
            'FkAnothertableRole' => [
                'table' => 'AnotherTable',
                'where' => [
                    'column_id' => 'extra_column_id'
                ],
            ]
        ])), $sourceSchema->describeTable('MyTable'));
        $this->assertEquals(new Record($this->schema->makeRecordType('AnotherTable'), new TableDescription([], [], [
            'FkAnothertableRole' => [
                'table' => 'MyTable',
                'where' => [
                    'extra_column_id' => 'column_id'
                ],
            ]
        ])), $sourceSchema->describeTable('AnotherTable'));
    }
}
