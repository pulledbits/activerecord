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
        $this->pdo = createMockPDOMultiple([
            '/SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'/' => [
                ['MyTable', 'BASE_TABLE'],
                ['AnotherTable', 'BASE_TABLE']
            ],
            '/SELECT COLUMN_NAME AS Field, COLUMN_TYPE AS Type, IS_NULLABLE AS `Null`, COLUMN_KEY AS `Key`, COLUMN_DEFAULT AS `Default`, EXTRA AS Extra, COLUMN_COMMENT AS Comment, CHARACTER_SET_NAME AS CharacterSet, COLLATION_NAME AS Collation FROM information_schema\.COLUMNS WHERE TABLE_SCHEMA = DATABASE\(\) AND TABLE_NAME = \'\w+\'/' => [
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
            ],
            '/SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` \/\**!50116 , c.update_rule, c.delete_rule \*\/ FROM information_schema.key_column_usage k \/\**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'\w+\' \*\/ WHERE k.table_name = \'\w+\' AND k.table_schema = \'\' \/\**!50116 AND c.constraint_schema = \'\' \*\/ AND k.`REFERENCED_COLUMN_NAME` is not NULL/' => [
                [
                    'CONSTRAINT_NAME' => 'fk_anothertable_role',
                    'COLUMN_NAME' => 'extra_column_id',
                    'REFERENCED_TABLE_NAME' => 'AnotherTable',
                    'REFERENCED_COLUMN_NAME' => 'column_id'
                ]
            ]
        ]);
        $this->connection = new Connection($this->pdo);
        $this->schema = new \pulledbits\ActiveRecord\SQL\Schema(new QueryFactory($this->connection));
    }

    public function testmakeFromSchemaManager_When_Default_Expect_ArrayWithRecordConfigurators()
    {
        $sourceSchema = SchemaFactory::makeFromPDO($this->connection, $this->pdo);

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
