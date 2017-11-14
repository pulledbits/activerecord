<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 25-1-17
 * Time: 15:50
 */

namespace pulledbits\ActiveRecord;


class EntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Entity
     */
    private $object;

    protected function setUp()
    {
        $schema = new class implements \pulledbits\ActiveRecord\Schema {
            private function convertResultSet(array $results) {
                return array_map(function(array $values) {
                    /**
                     * @var $this \pulledbits\ActiveRecord\Schema
                     */
                    $record = new \pulledbits\ActiveRecord\Entity($this, 'MyTable', $values);
                    $record->contains($values);
                    return $record;
                }, $results);
            }

            public function read(string $tableIdentifier, array $attributeIdentifiers, array $conditions): array
            {
                $resultset = [];
                if ($tableIdentifier === 'OtherTable') {
                    if ($attributeIdentifiers === [] && $conditions === []) {
                        $resultset = [
                            ['id' => '356'],
                            ['id' => '352'],
                            ['id' => '357'],
                            ['id' => '358'],
                            ['id' => '359']
                        ];
                    } elseif ($attributeIdentifiers === [] && $conditions === ['id' => '33']) {
                        $resultset = [
                            ['id' => '356']
                        ];
                    } elseif ($attributeIdentifiers === [] && $conditions === ['extra' => '5', 'id' => '33']) {
                        $resultset = [
                            ['id' => '357']
                        ];
                    } elseif ($attributeIdentifiers === [] && $conditions === ['extra' => '6', 'id' => '33']) {
                        $resultset = [
                            ['id' => '358']
                        ];
                    }
                }
                return $this->convertResultSet($resultset);
            }
            public function readFirst(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions): Record
            {
                $records = $this->read($entityTypeIdentifier, $attributeIdentifiers, $conditions);
                if (count($records) === 0) {
                    return $this->initializeRecord($entityTypeIdentifier, $conditions);
                }
                return $records[0];
            }

            public function update(string $tableIdentifier, array $values, array $conditions): int
            {
                if ($tableIdentifier === 'MyTable' && $values === ['number' => '2'] && $conditions === ['number' => '1']) {
                    return 1;
                }
                return 0;
            }

            public function create(string $tableIdentifier, array $values): int
            {
                if ($tableIdentifier === 'MyTable' && $values === ['number' => '1', 'role_id' => '33', 'pole_id' => '3654']) {
                    return 1;
                } elseif ($tableIdentifier === 'MyTable' && $values === ['name' => 'Test']) {
                    return 1;
                } elseif ($tableIdentifier === 'OtherTable' && $values === ['extra' => '6', 'id' => '33']) {
                    return 1;
                }
                return 0;
            }

            public function delete(string $tableIdentifier, array $conditions): int
            {
                if ($tableIdentifier === 'MyTable' && $conditions === ['number' => '1']) {
                    return 1;
                }
                return 0;
            }

            public function initializeRecord(string $entityTypeIdentifier, array $values): Record
            {
                return new \pulledbits\ActiveRecord\Entity($this, $entityTypeIdentifier, [], [], []);
            }

            public function executeProcedure(string $procedureIdentifier, array $arguments): void
            {
                // TODO: Implement executeProcedure() method.
            }
        };

        $values = [
            'number' => '1',
            'role_id' => '33',
            'pole_id' => '3654',
        ];
        $this->object = new Entity($schema, 'MyTable', ['number']);
        $this->object->contains($values);
        $this->object->references('FkOthertableRole', 'OtherTable', [
            'id' => 'role_id'
        ]);
    }

    public function testMissesRequiredValues_When_MissingRequiredProperties_Expect_True()
    {
        $this->assertFalse($this->object->missesRequiredValues());
        $this->object->requires(['name']);
        $this->assertTrue($this->object->missesRequiredValues());
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

    public function test__set_When_MissingRequiredProperty_Expect_NoChanges()
    {
        $this->object->requires(['name']);
        $this->assertEquals('1', $this->object->number);
        $this->object->number = '2';
        $this->assertEquals('1', $this->object->number);
    }

    public function testDelete_When_ExistingProperty_Expect_Value()
    {
        $this->assertEquals(1, $this->object->delete());
    }

    public function testCreate_When_DefaultState_Expect_AtLeastOneCreatedRecord()
    {
        $this->assertEquals(1, $this->object->create());
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     * @expectedExceptionMessageRegExp /^Required values are missing/
     */
    public function testCreate_When_RequiredValuesMissing_Expect_NoRecordsCreatedExpectError()
    {
        $this->object->requires(['name']);
        $this->object->contains([]);
        $this->assertEquals(0, $this->object->create());
        $this->object->contains(['name' => 'Test']);
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
    public function test__call_When_ExistingReferenceReferenceByCallWithAdditionalConditions_Expect_Value()
    {
        $record = $this->object->__call('referenceByFkOthertableRole', [["extra" => '6']]);
        $this->assertEquals('358', $record->id);
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     * @expectedExceptionMessageRegExp /^Reference does not exist/
     */
    public function test__call_When_NonExistingReference_Expect_Value() {
        $this->object->__call('fetchFirstByFkOthertableRoleWhichActuallyDoesNotExist', [["extra" => '5']]);
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
