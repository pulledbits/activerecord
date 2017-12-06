<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:36
 */

namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\SQL\Connection;
use pulledbits\ActiveRecord\SQL\QueryFactory;
use function pulledbits\ActiveRecord\Test\createMockPDOMultiple;

class SchemaTest extends \PHPUnit\Framework\TestCase
{
    private $pdo;
    private $connection;
    private $schema;

    protected function setUp()
    {
        $this->pdo = createMockPDOMultiple([]);

        $this->pdo->defineSchema('MySchema');
        $this->pdo->defineTables([
            ['Table_in_MySchema' => 'MyTable', 'Table_type' => 'BASE_TABLE'],
            ['Table_in_MySchema' => 'AnotherTable', 'Table_type' => 'BASE_TABLE'],
            ['Table_in_MySchema' => 'MyPerson', 'Table_type' => 'BASE_TABLE'],
            [
                'Table_in_MySchema' => 'MyView',
                'Table_type' => 'VIEW'
            ],
            [
                'Table_in_MySchema' => 'MyPerson_today',
                'Table_type' => 'VIEW'
            ],
            [
                'Table_in_MySchema' => 'MyPureView_bla',
                'Table_type' => 'VIEW'
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

        $this->pdo->defineColumns('MyPerson', [
            [
                'Field' => 'name',
                'Type' => 'INT',
                'Null' => 'YES',
                'Key' => 'PRI',
                'Default' => '',
                'Extra' => '',
                'Comment' => '',
                'CharacterSet' => '',
                'Collation' => ''
            ],
            [
                'Field' => 'birthdate',
                'Type' => 'INT',
                'Null' => 'YES',
                'Key' => 'PRI',
                'Default' => '',
                'Extra' => 'auto_increment',
                'Comment' => '',
                'CharacterSet' => '',
                'Collation' => ''
            ]
        ]);
        $this->pdo->defineIndexes('MyPerson', [
            [
                'Table' => 'MyPerson',
                'Non_unique' => '0',
                'Key_name' => 'PRIMARY',
                'Seq_in_index' => '1',
                'Column_name' => 'name',
                'Collation' => 'A',
                'Cardinality' => '1',
                'Sub_part' => null,
                'Packed' => null,
                'Null' => '',
                'Index_type' => 'BTREE',
                'Comment' => '',
                'Index_comment' => ''
            ],
            [
                'Table' => 'MyPerson',
                'Non_unique' => '0',
                'Key_name' => 'PRIMARY',
                'Seq_in_index' => '1',
                'Column_name' => 'birthdate',
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



        $this->connection = new Connection($this->pdo);
        $this->schema = new \pulledbits\ActiveRecord\SQL\Schema($this->connection, new QueryFactory('MySchema'), 'MySchema');
    }

    public function testConstructor_When_Default_Expect_ArrayWithRecordConfigurators()
    {

        $myTable = new TableDescription(['id'], ['id'], [
            'FkAnothertableRole' => [
                'table' => 'AnotherTable',
                'where' => [
                    'column_id' => 'extra_column_id'
                ],
            ]
        ]);
        $anotherTable = new TableDescription([], [], [
            'FkAnothertableRole' => [
                'table' => 'MyTable',
                'where' => [
                    'extra_column_id' => 'column_id'
                ],
            ]
        ]);

        $sourceSchema = new Schema($this->connection, $this->schema);

        $this->assertEquals($this->schema->makeRecord('MyTable', $myTable), $sourceSchema->makeRecord('MyTable'));
        $this->assertEquals($this->schema->makeRecord('AnotherTable', $anotherTable), $sourceSchema->makeRecord('AnotherTable'));
    }


    public function testDescribe_When_ViewAvailable_Expect_ArrayWithReadableClasses()
    {
        $schema = new Schema($this->connection, $this->schema);

        $tableDescription = $schema->makeRecord('MyView');

        $this->assertEquals($this->schema->makeRecord('MyView', new TableDescription()), $tableDescription);
    }


    public function testDescribe_When_ViewWithUnderscoreNoExistingTableAvailable_Expect_ArrayWithReadableClasses()
    {
        $schema = new Schema($this->connection, $this->schema);

        $tableDescription = $schema->makeRecord('MyView_bla');

        $this->assertEquals($this->schema->makeRecord('MyView_bla', new TableDescription()), $tableDescription);
    }

    public function testDescribe_When_ViewUsedWithExistingTableIdentifier_Expect_EntityTypeIdentifier()
    {

        $myPerson = new TableDescription(['name', 'birthdate'], [], []);

        $schema = new Schema($this->connection, $this->schema);

        $tableDescription = $schema->makeRecord('MyPerson_today');

        $this->assertEquals($this->schema->makeRecord('MyPerson_today', $myPerson), $tableDescription);
    }
}
