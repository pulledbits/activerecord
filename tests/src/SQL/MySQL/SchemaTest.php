<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:06
 */

namespace pulledbits\ActiveRecord\SQL\MySQL;

use pulledbits\ActiveRecord\SQL\Connection;
use pulledbits\ActiveRecord\SQL\MySQL;
use pulledbits\ActiveRecord\SQL\Record;
use function pulledbits\ActiveRecord\Test\createColumnResult;
use function pulledbits\ActiveRecord\Test\createConstraintResult;
use function pulledbits\ActiveRecord\Test\createIndexResult;
use function pulledbits\ActiveRecord\Test\createMockPDOStatement;
use function pulledbits\ActiveRecord\Test\createMockPDOStatementFail;
use function pulledbits\ActiveRecord\Test\createTableResult;
use function pulledbits\ActiveRecord\Test\createViewResult;

class SchemaTest extends \PHPUnit\Framework\TestCase
{
    private $pdo;

    /**
     * @var Schema
     */
    private $object;

    protected function setUp()
    {
        $this->pdo = \pulledbits\ActiveRecord\Test\createMockPDOCallback('MySchema');
        $connection = new Connection($this->pdo);
        $this->pdo->callback(function(string $query) {
            switch ($query) {
                case 'SHOW FULL TABLES IN MySchema':
                    return createMockPDOStatement([
                        createTableResult('MySchema', 'MyTable'),
                        createViewResult('MySchema', 'MyTable_MyView')
                    ]);


                case 'SHOW INDEX FROM MySchema.MyTable':
                    return createMockPDOStatement([
                        createIndexResult('MyTable', CONSTRAINT_KEY_PRIMARY, 'number')
                    ]);

                case 'SHOW FULL COLUMNS IN MySchema.MyTable':
                    return createMockPDOStatement([
                        createColumnResult('number', 'INT', true),
                        createColumnResult('role_id', 'INT', true),
                        createColumnResult('pole_id', 'INT', true)
                    ]);

                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'MyTable\' */ WHERE k.table_name = \'MyTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'MyTable\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'MyTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([
                        createConstraintResult('fk_othertable_role', 'role_id', 'OtherTable', 'id')
                    ]);
                case 'SHOW INDEX FROM MySchema.MyTable2':
                    return createMockPDOStatement([
                        createIndexResult('MyTable2', CONSTRAINT_KEY_PRIMARY, 'number')
                    ]);

                case 'SHOW FULL COLUMNS IN MySchema.MyTable2':
                    return createMockPDOStatement([
                        createColumnResult('number', 'INT', true),
                        createColumnResult('name', 'INT', false),
                        createColumnResult('role_id', 'INT', true),
                        createColumnResult('role2_id', 'INT', true, true),
                        createColumnResult('pole_id', 'INT', true)
                    ]);

                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'MyTable2\' */ WHERE k.table_name = \'MyTable2\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'MyTable2\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'MyTable2\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([
                        createConstraintResult('fk_othertable_role', 'role_id', 'OtherTable', 'id'),
                        createConstraintResult('fk_othertable_role', 'role2_id', 'OtherTable', 'id2')
                    ]);
            }
        });
        $this->object = new MySQL\Schema(new QueryFactory($connection), 'MySchema');
    }

    public function testUpdateWhere_When_DefaultState_Expect_SQLUpdateQueryWithWhereStatementAndParameters() {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'UPDATE MySchema.MyTable SET werkvorm = ' . $matchedParameters[0] . ' WHERE id = ' . $matchedParameters[1]:
                    return createMockPDOStatement(1, $matchedParameters, [
                        'My Name',
                        '3'
                    ]);
            }
        });
        $this->assertEquals(1, $this->object->update('MyTable', ['werkvorm' => 'My Name'], ['id' => '3']));
    }

    public function testInsertValue_When_DefaultState_Expect_SQLInsertQueryWithPreparedValues() {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'INSERT INTO MySchema.MyTable (werkvorm, id) VALUES ('.$matchedParameters[0].', '.$matchedParameters[1].')':
                    return createMockPDOStatement(1, $matchedParameters, [
                        'My Name',
                        '3'
                    ]);
            }
        });
        $this->assertEquals(1, $this->object->create('MyTable', ['werkvorm' => 'My Name', 'id' => '3']));
    }

    public function testSelectFrom_When_NoConditions_Expect_WhereLessSQL() {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'SHOW INDEX FROM MySchema.MyTable':
                case 'SHOW FULL COLUMNS IN MySchema.MyTable':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'MyTable\' */ WHERE k.table_name = \'MyTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'MyTable\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'MyTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);

                case 'SELECT * FROM MySchema.MyTable':
                    return createMockPDOStatement([
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
                    ], [], []);
            }
        });

        $records = $this->object->read('MyTable', [], []);

        $this->assertCount(10, $records);
        $this->assertEquals('BlaBlaNoWhere', $records[0]->werkvorm);
    }

    public function testSelectFrom_When_NoColumnIdentifiers_Expect_SQLSelectAsteriskQueryAndCallbackUsedForFetchAll() {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'SHOW INDEX FROM MySchema.MyTable':
                case 'SHOW FULL COLUMNS IN MySchema.MyTable':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'MyTable\' */ WHERE k.table_name = \'MyTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'MyTable\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'MyTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);

                case 'SELECT * FROM MySchema.MyTable WHERE id = ' . $matchedParameters[0]:
                    return createMockPDOStatement([
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
                    ], $matchedParameters, ['1']);
            }
        });

        $records = $this->object->read('MyTable', [], ['id' => '1']);

        $this->assertCount(10, $records);
        $this->assertEquals('BlaBla', $records[0]->werkvorm);
    }

    public function testSelectFrom_When_DefaultState_Expect_SQLSelectQueryAndCallbackUsedForFetchAll() {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'SHOW INDEX FROM MySchema.MyTable':
                case 'SHOW FULL COLUMNS IN MySchema.MyTable':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'MyTable\' */ WHERE k.table_name = \'MyTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'MyTable\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'MyTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);

                case 'SELECT id, werkvorm FROM MySchema.MyTable WHERE id = ' . $matchedParameters[0]:
                    return createMockPDOStatement([
                        [
                            'werkvorm' => 'Bla'
                        ],
                        [],
                        [],
                        []
                    ], $matchedParameters, ['1']);
            }
        });

        $records = $this->object->read('MyTable', ['id', 'werkvorm'], ['id' => '1']);

        $this->assertCount(4, $records);
        $this->assertEquals('Bla', $records[0]->werkvorm);
    }


    public function testRead_When_EntityNotExists_Expect_EmptyTableDescription()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'SELECT id, werkvorm FROM MySchema.NotExisting WHERE id = ' . $matchedParameters[0]:
                    return createMockPDOStatementFail(false);

                case 'SHOW INDEX FROM MySchema.NotExisting':
                case 'SHOW FULL COLUMNS IN MySchema.NotExisting':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'NotExisting\' */ WHERE k.table_name = \'NotExisting\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'NotExisting\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'NotExisting\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);

            }
        });


        $this->expectException('\PHPUnit\Framework\Error\Error');
        $this->expectExceptionMessageRegExp('/^Failed executing query/');
        $this->object->read('NotExisting', ['id', 'werkvorm'], ['id' => '1']);
    }

    public function testRead_When_EntityIsView_Expect_EmptyTableDescriptionForViewIdentifier()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'SELECT * FROM MySchema.MyView':
                    return createMockPDOStatement([['a' => 'b']]);

                case 'SHOW INDEX FROM MySchema.MyView':
                case 'SHOW FULL COLUMNS IN MySchema.MyView':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'MyView\' */ WHERE k.table_name = \'MyView\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'MyView\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'MyView\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);
            }
        });

        $expectedRecord = new Record(new Table($this->object, 'MyView'));
        $expectedRecord->contains(['a' => 'b']);
        $this->assertEquals([   $expectedRecord    ], $this->object->read('MyView', [], []));
    }

    public function testRead_When_EntityIsViewWrappedAroundOtherTable_Expect_EntityTypeForWrappedTable()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {

                case 'SELECT * FROM MySchema.MyTable_MyView':
                    return createMockPDOStatement([['a' => 'b']]);

                case 'SHOW INDEX FROM MySchema.MyTable_MyView':
                case 'SHOW FULL COLUMNS IN MySchema.MyTable_MyView':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'MyTable_MyView\' */ WHERE k.table_name = \'MyTable_MyView\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'MyTable_MyView\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'MyTable_MyView\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);

                case 'SHOW INDEX FROM MySchema.MyView':
                case 'SHOW FULL COLUMNS IN MySchema.MyView':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'MyView\' */ WHERE k.table_name = \'MyView\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'MyView\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'MyView\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);
            }
        });

        $expectedRecord = new Record(new Table($this->object, 'MyTable'));
        $expectedRecord->contains(['a' => 'b']);
        $this->assertEquals([   $expectedRecord    ], $this->object->read('MyTable_MyView', [], []));
    }


    public function testRead_When_ViewWrappingBaseTable_Expect_PropertiesFromBaseTable() {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'SHOW INDEX FROM MySchema.MyPerson_today':
                case 'SHOW FULL COLUMNS IN MySchema.MyPerson_today':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'MyPerson_today\' */ WHERE k.table_name = \'MyPerson_today\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'MyPerson_today\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'MyPerson_today\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);

                case 'SELECT id, name FROM MySchema.MyPerson_today WHERE id = ' . $matchedParameters[0]:
                    return createMockPDOStatement([
                        [
                            'werkvorm' => 'Bla'
                        ],
                        [],
                        [],
                        []
                    ], $matchedParameters, ['1']);
            }
        });

        $records = $this->object->read('MyPerson_today', ['id', 'name'], ['id' => '1']);

        $this->assertCount(4, $records);
        $this->assertEquals('Bla', $records[0]->werkvorm);
    }

    public function testDeleteFrom_When_DefaultState_Expect_SQLDeleteQuery() {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'DELETE FROM MySchema.MyTable WHERE id = ' . $matchedParameters[0]:
                    return createMockPDOStatement(1, $matchedParameters, ['3']);
            }
        });
        $this->assertEquals(1, $this->object->delete('MyTable', ['id' => '3']));
    }

    public function testDeleteFrom_When_Erroneous_Expect_Warning() {
        $this->expectException('\PHPUnit\Framework\Error\Error');
        $this->expectExceptionMessageRegExp('/^Failed executing query/');
        $this->assertEquals(0, $this->object->delete('MyTable', ['sid' => '3']));
    }

    public function testExecuteProcedure_When_MissingProcedureCalled_Expect_Error() {
        $this->expectException('\PHPUnit\Framework\Error\Error');
        $this->expectExceptionMessageRegExp('/^Failed executing query/');
        $this->object->executeProcedure('missing_procedure', ['3', 'Foobar']);
    }

    public function testExecuteProcedure_When_ExistingProcedure_Expect_ProcedureToBeCalled() {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'CALL MySchema.existingProcedure(' . $matchedParameters[0] . ', ' . $matchedParameters[1] . ')':
                    return createMockPDOStatement(1, $matchedParameters, ['3', 'Foobar']);
            }
        });
        $this->assertNull($this->object->executeProcedure('existingProcedure', ['3', 'Foobar']));
    }
}