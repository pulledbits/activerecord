<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:06
 */

namespace ActiveRecord\SQL;


use ActiveRecord\Schema\Asset;

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
        $asset = new class implements Asset {

            public function executeRecordClassConfigurator(string $path, array $values): \ActiveRecord\Record
            {
                return new \ActiveRecord\Record($this, $values, [], $values);
            }

            public function select(array $columnIdentifiers, array $whereParameters)
            {}

            public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters)
            {}

            public function insert(array $values)
            {}

            public function update(array $setParameters, array $whereParameters)
            {}

            public function delete(array $whereParameters)
            {}
        };

        $records = $this->object->selectFrom('activiteit', ['id', 'werkvorm'], ['id' => '1'], function(\Closure $recordConfigurator) use ($asset) {
            return $recordConfigurator($asset);
        });

        $this->assertCount(4, $records);
        $this->assertEquals('Bla', $records[0]->werkvorm);
    }

    public function testDeleteFrom_When_DefaultState_Expect_SQLDeleteQuery() {
        $this->assertEquals(1, $this->object->deleteFrom('activiteit', ['id' => '3']));
    }
}