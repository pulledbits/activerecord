<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:06
 */

namespace pulledbits\ActiveRecord\SQL;

use PHPUnit\Framework\Error\Error;
use pulledbits\ActiveRecord\SQL\Meta\SchemaFactory;

class SchemaTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var Schema
     */
    private $object;

    protected function setUp()
    {
        $pdo = \pulledbits\ActiveRecord\Test\createMockPDOMultiple([
            '/SELECT \* FROM MySchema.MyTable WHERE id = :\w+$/' => [
                [
                    'werkvorm' => 'BlaBla'
                ],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                []
            ],
            '/SELECT \* FROM MySchema.MyTable$/' => [
                [
                    'werkvorm' => 'BlaBlaNoWhere'
                ],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                []
            ],
            '/SELECT id AS _id, werkvorm AS _werkvorm FROM MySchema.MyTable WHERE id = :param1$/' => [
                [],
                [],
                [],
                [],
                []
            ],
            '/^SELECT id AS _id, werkvorm AS _werkvorm FROM MySchema.MyTable WHERE werkvorm = :\w+$/' => [
                []
            ],
            '/^UPDATE MySchema.MyTable SET werkvorm = :\w+ WHERE id = :\w+$/' => 1,
            '/^INSERT INTO MySchema.MyTable \(werkvorm, id\) VALUES \(:\w+, :\w+\)$/' => 1,
            '/SELECT id, werkvorm FROM MySchema.MyTable WHERE id = :\w+$/' => [
                [
                    'werkvorm' => 'Bla'
                ],
                [],
                [],
                []
            ],
            '/SELECT id, werkvorm FROM MySchema.MyTable WHERE id = :\w+ AND foo = :\w+$/' => [],
            '/^DELETE FROM MySchema.MyTable WHERE id = :\w+$/' => 1,
            '/^DELETE FROM MySchema.MyTable WHERE sid = :\w+$/' => false,

            '/^INSERT INTO MySchema.MyTable \(name. foo2\) VALUES \(:\w+, :\w+\)$/' => 1,
            '/^INSERT INTO MySchema.MyTable \(name. foo3, foo4\) VALUES \(:\w+, :\w+, :\w+\)$/' => 1,
            '/^CALL MySchema.missing_procedure\(:\w+, :\w+\)/' => false,
            '/SELECT id, name FROM MySchema.MyPerson_today WHERE id = :\w+/' => [
                [
                    'werkvorm' => 'Bla'
                ],
                [],
                [],
                []
            ]
        ]);

        $pdo->defineSchema('MySchema');
        $pdo->defineTables([
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

        $pdo->defineColumns('MyTable', [
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
        $pdo->defineConstraints('MyTable', [
            [
                'CONSTRAINT_NAME' => 'fk_anothertable_role',
                'COLUMN_NAME' => 'extra_column_id',
                'REFERENCED_TABLE_NAME' => 'AnotherTable',
                'REFERENCED_COLUMN_NAME' => 'column_id'
            ]
        ]);
        $pdo->defineIndexes('MyTable', [
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


        $pdo->defineColumns('AnotherTable', [
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
        $pdo->defineConstraints('AnotherTable', []);

        $pdo->defineColumns('MyPerson', [
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
        $pdo->defineIndexes('MyPerson', [
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
        $connection = new Connection($pdo);
        $this->object = new \pulledbits\ActiveRecord\SQL\Schema($connection, new QueryFactory(), 'MySchema');
    }

    public function testUpdateWhere_When_DefaultState_Expect_SQLUpdateQueryWithWhereStatementAndParameters() {
        $this->assertEquals(1, $this->object->update('MyTable', ['werkvorm' => 'My Name'], ['id' => '3']));
    }

    public function testInsertValue_When_DefaultState_Expect_SQLInsertQueryWithPreparedValues() {
        $this->assertEquals(1, $this->object->create('MyTable', ['werkvorm' => 'My Name', 'id' => '3']));
    }

    public function testSelectFrom_When_NoConditions_Expect_WhereLessSQL() {
        $records = $this->object->read('MyTable', [], []);

        $this->assertCount(10, $records);
        $this->assertEquals('BlaBlaNoWhere', $records[0]->werkvorm);
    }

    public function testSelectFrom_When_NoColumnIdentifiers_Expect_SQLSelectAsteriskQueryAndCallbackUsedForFetchAll() {
        $records = $this->object->read('MyTable', [], ['id' => '1']);

        $this->assertCount(10, $records);
        $this->assertEquals('BlaBla', $records[0]->werkvorm);
    }

    public function testSelectFrom_When_DefaultState_Expect_SQLSelectQueryAndCallbackUsedForFetchAll() {
        $records = $this->object->read('MyTable', ['id', 'werkvorm'], ['id' => '1']);

        $this->assertCount(4, $records);
        $this->assertEquals('Bla', $records[0]->werkvorm);
    }

    public function testRead_When_ViewWrappingBaseTable_Expect_PropertiesFromBaseTable() {
        $records = $this->object->read('MyPerson_today', ['id', 'name'], ['id' => '1']);

        $this->assertCount(4, $records);
        $this->assertEquals('Bla', $records[0]->werkvorm);
    }

/*

    public function testDescribe_When_ViewAvailable_Expect_ArrayWithReadableClasses()
    {
        $schema = new Schema($this->connection, $this->object);

        $tableDescription = $schema->makeRecord('MyView');

        $this->assertEquals($this->object->makeRecord('MyView', new TableDescription()), $tableDescription);
    }


    public function testDescribe_When_ViewWithUnderscoreNoExistingTableAvailable_Expect_ArrayWithReadableClasses()
    {
        $schema = new Schema($this->connection, $this->object);

        $tableDescription = $schema->makeRecord('MyView_bla');

        $this->assertEquals($this->object->makeRecord('MyView_bla', new TableDescription()), $tableDescription);
    }

    public function testDescribe_When_ViewUsedWithExistingTableIdentifier_Expect_EntityTypeIdentifier()
    {

        $myPerson = new TableDescription(['name', 'birthdate'], [], []);

        $schema = new Schema($this->connection, $this->object);

        $tableDescription = $schema->makeRecord('MyPerson_today');

        $this->assertEquals($this->object->makeRecord('MyPerson_today', $myPerson), $tableDescription);
    }*/

    public function testDeleteFrom_When_DefaultState_Expect_SQLDeleteQuery() {
        $this->assertEquals(1, $this->object->delete('MyTable', ['id' => '3']));
    }

    public function testDeleteFrom_When_Erroneous_Expect_Warning() {
        $this->expectException('\PHPUnit\Framework\Error\Error');
        $this->expectExceptionMessageRegExp('/^Failed executing query/');
        $this->assertEquals(0, $this->object->delete('MyTable', ['sid' => '3']));
    }

    public function testExecuteProcedure_When_ExistingProcedure_Expect_ProcedureToBeCalled() {
        $this->expectException('\PHPUnit\Framework\Error\Error');
        $this->expectExceptionMessageRegExp('/^Failed executing query/');
        $this->object->executeProcedure('missing_procedure', ['3', 'Foobar']);
    }
}