<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:06
 */

namespace pulledbits\ActiveRecord\SQL;

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
            '/^CALL MySchema.existingProcedure\(:\w+, :\w+\)/' => null,
            '/SELECT id, name FROM MySchema.MyPerson_today WHERE id = :\w+/' => [
                [
                    'werkvorm' => 'Bla'
                ],
                [],
                [],
                []
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

    public function testDeleteFrom_When_DefaultState_Expect_SQLDeleteQuery() {
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
        $this->assertNull($this->object->executeProcedure('existingProcedure', ['3', 'Foobar']));
    }
}