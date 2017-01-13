<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:06
 */

namespace ActiveRecord;


class SchemaTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Schema
     */
    private $object;

    protected function setUp()
    {
        $this->object = new Schema('\Test\Record', \ActiveRecord\Test\createMockPDOMultiple([
            '/SELECT id AS _id, werkvorm AS _werkvorm FROM activiteit WHERE id = :param1/' => [
                [],
                [],
                [],
                [],
                []
            ],
            '/^SELECT id AS _id, werkvorm AS _werkvorm FROM activiteit WHERE werkvorm = :\w+$/' => [
                []
            ],
            '/^UPDATE activiteit SET werkvorm = :\w+ WHERE id = :\w+$/' => 1,
            '/SELECT id, werkvorm FROM activiteit WHERE id = :\w+/' => [
                [],
                []
            ],
        ]));
    }


    public function testTransformTableIdentifierToRecordClassIdentifier_When_TableIdentifierSupplied_Expect_TableIdPrefixedWithTargetNamespace()
    {
        $this->assertEquals('\Test\Record\activiteit', $this->object->transformTableIdentifierToRecordClassIdentifier('activiteit'));
    }

    public function testExecute_When_WhenProperQueryWithNamedParametersSupplied_Expect_PDOStatementWithFiveRecords()
    {
        $statement = $this->object->execute('SELECT id AS _id, werkvorm AS _werkvorm FROM activiteit WHERE id = :param1', [':param1' => '1']);
        $this->assertCount(5, $statement->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function testExecuteWhere_When_DefaultState_Expect_SQLQueryWithWhereStatementAndParameters() {
        $statement = $this->object->executeWhere('SELECT id AS _id, werkvorm AS _werkvorm FROM activiteit', ['werkvorm' => 'My Name']);
        $this->assertCount(1, $statement->fetchAll(\PDO::FETCH_ASSOC));

    }

    public function testUpdateWhere_When_DefaultState_Expect_SQLUpdateQueryWithWhereStatementAndParameters() {
        $statement = $this->object->updateWhere('activiteit', ['werkvorm' => 'My Name'], ['id' => '3']);
        $this->assertEquals(1, $statement->rowCount());

    }
}