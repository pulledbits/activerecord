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
            private function convertResultSet(array $results) {
                return array_map(function(array $values) {
                    return new \ActiveRecord\Entity($this, 'MyTable', $values, [], $values);
                }, $results);
            }

            public function read(string $tableIdentifier, array $columnIdentifiers, array $whereParameters): array
            {
                $resultset = [];
                if ($tableIdentifier === 'OtherTable' && $columnIdentifiers === ['id'] && $whereParameters === ['id' => '33']) {
                    $resultset = [
                        ['id' => '33']
                    ];
                }
                return $this->convertResultSet($resultset);
            }

            public function update(string $tableIdentifier, array $setParameters, array $whereParameters): int
            {
                if ($tableIdentifier === 'MyTable' && $setParameters === ['number' => '2'] && $whereParameters === ['number' => '1']) {
                    return 1;
                }
                return 0;
            }

            public function create(string $tableIdentifier, array $values): int
            {
                // TODO: Implement insertValues() method.
            }

            public function delete(string $tableIdentifier, array $whereParameters): int
            {
                if ($tableIdentifier === 'MyTable' && $whereParameters === ['number' => '1']) {
                    return 1;
                }
                return 0;
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
        $this->object = new Entity($schema, 'MyTable', $primaryKey, $references, $values);
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
