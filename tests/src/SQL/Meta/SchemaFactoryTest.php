<?php

namespace pulledbits\ActiveRecord\SQL\Meta;


use pulledbits\ActiveRecord\SQL\Connection;
use pulledbits\ActiveRecord\SQL\QueryFactory;
use function pulledbits\ActiveRecord\Test\createMockPDOMultiple;

class SchemaFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $pdo;
    private $connection;
    private $schema;

    protected function setUp()
    {
        $this->pdo = createMockPDOMultiple([]);

        $this->pdo->defineTables([
            ['MyTable', 'Table_type' => 'BASE_TABLE'],
            ['AnotherTable', 'Table_type' => 'BASE_TABLE']
        ]);
        $this->pdo->defineViews([
            [
                'TABLE_NAME' => 'MyView',
                'VIEW_DEFINITION' => 'SELECT * FROM MyTable;'
            ]
        ]);

        $this->pdo->defineColumns('MyTable', [
            [
            'Field' => 'id',
            'Type' => 'INT',
            'Null' => 'NO',
            'Key' => 'PRI',
            'Default' => '',
            'Extra' => '',
            'Comment' => '',
            'CharacterSet' => '',
            'Collation' => ''
        ],
            [
                'Field' => 'id2',
                'Type' => 'INT',
                'Null' => 'NO',
                'Key' => 'PRI',
                'Default' => '',
                'Extra' => 'auto_increment',
                'Comment' => '',
                'CharacterSet' => '',
                'Collation' => ''
            ],
            [
                'Field' => 'extra_column_id',
                'Type' => 'INT',
                'Null' => 'YES',
                'Key' => '',
                'Default' => '',
                'Extra' => '',
                'Comment' => '',
                'CharacterSet' => '',
                'Collation' => ''
            ]
        ]);
        $this->pdo->defineConstraints('MyTable', [
            [
                'CONSTRAINT_NAME' => 'fk_anothertable_role',
                'COLUMN_NAME' => 'extra_column_id',
                'REFERENCED_TABLE_NAME' => 'AnotherTable',
                'REFERENCED_COLUMN_NAME' => 'column_id'
            ],
            [
            'CONSTRAINT_NAME' => 'fk_anothertable_role',
            'COLUMN_NAME' => 'extra_column_id2',
            'REFERENCED_TABLE_NAME' => 'AnotherTable',
            'REFERENCED_COLUMN_NAME' => 'column_id2'
        ]
        ]);
        $this->pdo->defineIndexes('MyTable', [
            [
            'Table' => 'MyTable',
            'Non_unique' => '0',
            'Key_name' => 'PRIMARY',
            'Seq_in_index' => '1',
            'Column_name' => 'id',
            'Collation' => 'A',
            'Cardinality' => '1',
            'Sub_part' => null,
            'Packed' => null,
            'Null' => '',
            'Index_type' => 'BTREE',
            'Comment' => '',
            'Index_comment' => ''
            ]
        ]);


        $this->pdo->defineColumns('AnotherTable', [
            [
                'Field' => 'column_id',
                'Type' => 'INT',
                'Null' => 'YES',
                'Key' => '',
                'Default' => '',
                'Extra' => '',
                'Comment' => '',
                'CharacterSet' => '',
                'Collation' => ''
            ]
        ]);
        $this->pdo->defineConstraints('AnotherTable', []);

        $this->connection = new Connection($this->pdo);
        $this->schema = new \pulledbits\ActiveRecord\SQL\Schema(new QueryFactory($this->connection));
    }

    public function testmakeFromSchemaManager_When_Default_Expect_ArrayWithRecordConfigurators()
    {
        $sourceSchema = SchemaFactory::makeFromConnection($this->connection);

        $this->assertEquals($this->schema->makeRecordType('MyTable', new TableDescription(['id'], ['id'], [
            'FkAnothertableRole' => [
                'table' => 'AnotherTable',
                'where' => [
                    'column_id' => 'extra_column_id',
                    'column_id2' => 'extra_column_id2'
                ],
            ]
        ])), $sourceSchema->describeTable('MyTable'));
        $this->assertEquals($this->schema->makeRecordType('AnotherTable', new TableDescription([], [], [
            'FkAnothertableRole' => [
                'table' => 'MyTable',
                'where' => [
                    'extra_column_id' => 'column_id',
                    'extra_column_id2' => 'column_id2'
                ],
            ]
        ])), $sourceSchema->describeTable('AnotherTable'));
    }
}
