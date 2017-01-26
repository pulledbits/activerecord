<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:06
 */

namespace ActiveRecord\SQL;

class SchemaTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Schema
     */
    private $object;

    protected function setUp()
    {
        $recordConfiguration = new \ActiveRecord\RecordFactory(sys_get_temp_dir());
        $this->object = new Schema($recordConfiguration, \ActiveRecord\Test\createMockPDOMultiple([
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
            '/^INSERT INTO activiteit \(werkvorm, id\) VALUES \(:\w+, :\w+\)$/' => 1,
            '/SELECT id, werkvorm FROM activiteit WHERE id = :\w+/' => [
                [
                    'werkvorm' => 'Bla'
                ],
                [],
                [],
                []
            ],
            '/^DELETE FROM activiteit WHERE id = :\w+$/' => 1,
        ]));
    }

    public function testUpdateWhere_When_DefaultState_Expect_SQLUpdateQueryWithWhereStatementAndParameters() {
        $this->assertEquals(1, $this->object->updateWhere('activiteit', ['werkvorm' => 'My Name'], ['id' => '3']));

    }

    public function testInsertValue_When_DefaultState_Expect_SQLInsertQueryWithPreparedValues() {
        $this->assertEquals(1, $this->object->insertValues('activiteit', ['werkvorm' => 'My Name', 'id' => '3']));
    }

    public function testSelectFrom_When_DefaultState_Expect_SQLSelectQueryAndCallbackUsedForFetchAll() {
        $records = $this->object->readFrom('activiteit', ['id', 'werkvorm'], ['id' => '1']);

        $this->assertCount(4, $records);
        $this->assertEquals('Bla', $records[0]->werkvorm);
    }

    public function testDeleteFrom_When_DefaultState_Expect_SQLDeleteQuery() {
        $this->assertEquals(1, $this->object->deleteFrom('activiteit', ['id' => '3']));
    }
}