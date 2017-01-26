<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 25-1-17
 * Time: 15:50
 */

namespace ActiveRecord;


class EntityTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $schema = new class implements \ActiveRecord\Schema {
            private function convertResultSet(array $results, \ActiveRecord\Schema\EntityType $entityType) {
                return array_map(function(array $values) use ($entityType) {
                    return new \ActiveRecord\Entity($entityType, $this, 'MyTable', $values, [], $values);
                }, $results);
            }

            public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters, \ActiveRecord\Schema\EntityType $entityType): array
            {
                $resultset = [];
                if ($tableIdentifier === 'OtherTable' && $columnIdentifiers === ['id'] && $whereParameters === ['id' => '33']) {
                    $resultset = [
                        ['id' => '33']
                    ];
                }
                return $this->convertResultSet($resultset, $entityType);
            }

            public function updateWhere(string $tableIdentifier, array $setParameters, array $whereParameters): int
            {
                if ($tableIdentifier === 'MyTable' && $setParameters === ['number' => '2'] && $whereParameters === ['number' => '1']) {
                    return 1;
                }
                return 0;
            }

            public function insertValues(string $tableIdentifier, array $values): int
            {
                // TODO: Implement insertValues() method.
            }

            public function deleteFrom(string $tableIdentifier, array $whereParameters): int
            {
                if ($tableIdentifier === 'MyTable' && $whereParameters === ['number' => '1']) {
                    return 1;
                }
                return 0;
            }
        };

        $asset = new class implements \ActiveRecord\Schema\EntityType{

            public function executeEntityConfigurator(string $path, array $values): \ActiveRecord\Entity
            {}

            public function select(array $columnIdentifiers, array $whereParameters) : array
            {}

            public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters) : array
            {
            }

            public function insert(array $values) : int
            { }

            public function update(array $setParameters, array $whereParameters) : int
            {
            }

            public function delete(array $whereParameters) : int
            {
            }
        };
        $primaryKey = [
            'number' => '1'
        ];
        $references = [
            'FkOthertableRole' => [
                'table' => 'OtherTable',
                'where' => [
                    'id' => 'role_id'
                ],
            ]
        ];
        $values = [
            'number' => '1',
            'role_id' => '33',
        ];
        $this->object = new Entity($asset, $schema, 'MyTable', $primaryKey, $references, $values);
    }

    public function test__get_When_ExistingProperty_Expect_Value()
    {
        $value = $this->object->number;
        $this->assertEquals('1', $value);
    }

    public function test__set_When_ExistingProperty_Expect_ValueChanged()
    {
        $this->assertEquals('1', $this->object->number);
        $this->object->number = '2';
        $this->assertEquals('2', $this->object->number);
    }

    public function testDelete_When_ExistingProperty_Expect_Value()
    {
        $this->assertEquals(1, $this->object->delete());
    }

    public function test__call_When_ExistingReferenceFetchByCall_Expect_Value()
    {
        $records = $this->object->fetchByFkOthertableRole();
        $this->assertEquals('33', $records[0]->id);
    }
}
