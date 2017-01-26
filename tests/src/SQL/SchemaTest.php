<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:06
 */

namespace ActiveRecord\SQL;


use ActiveRecord\Schema\EntityType;

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
        $asset = new class implements EntityType {

            public function executeEntityConfigurator(string $path, array $values): \ActiveRecord\Entity
            {
                $schema = new class implements \ActiveRecord\Schema {

                    public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters, \ActiveRecord\Schema\EntityType $entityType): array
                    {
                        // TODO: Implement selectFrom() method.
                    }

                    public function updateWhere(string $tableIdentifier, array $setParameters, array $whereParameters): int
                    {
                        // TODO: Implement updateWhere() method.
                    }

                    public function insertValues(string $tableIdentifier, array $values): int
                    {
                        // TODO: Implement insertValues() method.
                    }

                    public function deleteFrom(string $tableIdentifier, array $whereParameters): int
                    {
                        // TODO: Implement deleteFrom() method.
                    }
                };

                return new \ActiveRecord\Entity($this, $schema, 'MyTable', $values, [], $values);
            }

            public function select(array $columnIdentifiers, array $whereParameters) : array
            {}

            public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters) : array
            {}

            public function insert(array $values) : int
            {}

            public function update(array $setParameters, array $whereParameters) : int
            {}

            public function delete(array $whereParameters) : int
            {}
        };

        $records = $this->object->selectFrom('activiteit', ['id', 'werkvorm'], ['id' => '1'], $asset);

        $this->assertCount(4, $records);
        $this->assertEquals('Bla', $records[0]->werkvorm);
    }

    public function testDeleteFrom_When_DefaultState_Expect_SQLDeleteQuery() {
        $this->assertEquals(1, $this->object->deleteFrom('activiteit', ['id' => '3']));
    }
}