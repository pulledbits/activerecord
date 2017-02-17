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

    /**
     * @var Entity
     */
    private $object;

    protected function setUp()
    {
        $schema = new class implements \ActiveRecord\Schema {
            private function convertResultSet(array $results) {
                return array_map(function(array $values) {
                    /**
                     * @var $this \ActiveRecord\Schema
                     */
                    return new \ActiveRecord\Entity($this, 'MyTable', $values, [], $values);
                }, $results);
            }

            public function read(string $tableIdentifier, array $columnIdentifiers, array $whereParameters): array
            {
                $resultset = [];
                if ($tableIdentifier === 'OtherTable') {
                    if ($columnIdentifiers === [] && $whereParameters === []) {
                        $resultset = [
                            ['id' => '356'],
                            ['id' => '352'],
                            ['id' => '357'],
                            ['id' => '358'],
                            ['id' => '359']
                        ];
                    } elseif ($columnIdentifiers === [] && $whereParameters === ['id' => '33']) {
                        $resultset = [
                            ['id' => '356']
                        ];
                    } elseif ($columnIdentifiers === [] && $whereParameters === ['extra' => '5', 'id' => '33']) {
                        $resultset = [
                            ['id' => '357']
                        ];
                    }
                }
                return $this->convertResultSet($resultset);
            }
            public function readFirst(string $entityTypeIdentifier, array $columnIdentifiers, array $conditions): Record
            {
                $records = $this->read($entityTypeIdentifier, $columnIdentifiers, $conditions);
                if (count($records) === 0) {
                    return new \ActiveRecord\Entity($this, $entityTypeIdentifier, [], [], []);
                }
                return $records[0];
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
                if ($tableIdentifier === 'MyTable' && $values === ['number' => '1', 'role_id' => '33', 'pole_id' => '3654']) {
                    return 1;
                }
                return 0;
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
            'pole_id' => '3654',
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

    public function testCreate_When_DefaultState_Expect_AtLeastOneCreatedRecord()
    {
        $this->assertEquals(1, $this->object->create());
    }

    public function test__call_When_ExistingReferenceFetchByCall_Expect_Value()
    {
        $records = $this->object->__call('fetchByFkOthertableRole', []);
        $this->assertEquals('356', $records[0]->id);
    }

    public function test__call_When_ExistingReferenceFetchByCallWithAdditionalConditions_Expect_Value()
    {
        $records = $this->object->__call('fetchByFkOthertableRole', [["extra" => '5']]);
        $this->assertEquals('357', $records[0]->id);
    }

    public function test__call_When_ExistingReferenceFetchFirstByCall_Expect_Value()
    {
        $record = $this->object->__call('fetchFirstByFkOthertableRole', []);
        $this->assertEquals('356', $record->id);
    }
    public function test__call_When_ExistingReferenceFetchFirstByCallWithAdditionalConditions_Expect_Value()
    {
        $record = $this->object->__call('fetchFirstByFkOthertableRole', [["extra" => '5']]);
        $this->assertEquals('357', $record->id);
    }


    public function testRead_When_NoConditionsGiven_Expect_FullResultSet()
    {
        $records = $this->object->read("OtherTable", []);
        $this->assertEquals('356', $records[0]->id);
        $this->assertCount(5, $records);
    }
    public function testRead_When_ConditionsGiven_Expect_PartialResultSet()
    {
        $records = $this->object->read("OtherTable", ['id' => 'role_id']);
        $this->assertEquals('356', $records[0]->id);
        $this->assertCount(1, $records);
    }
    public function testReadFirst_When_NoConditionsGiven_Expect_OnlyFirstRecord()
    {
        $record = $this->object->readFirst("OtherTable", []);
        $this->assertEquals('356', $record->id);
    }
    public function testReadFirst_When_NoMatchingConditionsGiven_Expect_Null()
    {
        $record = $this->object->readFirst("OtherTable", ['id' => 'pole_id']);
        $this->assertNull($record->id);
    }
}
