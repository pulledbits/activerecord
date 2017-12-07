<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 25-1-17
 * Time: 15:50
 */

namespace pulledbits\ActiveRecord\SQL;


use pulledbits\ActiveRecord\Record;
use pulledbits\ActiveRecord\Result;
use pulledbits\ActiveRecord\SQL;
use pulledbits\ActiveRecord\SQL\MySQL\EntityType;
use pulledbits\ActiveRecord\SQL\MySQL\EntityTypes;
use function pulledbits\ActiveRecord\Test\createColumnResult;
use function pulledbits\ActiveRecord\Test\createConstraintResult;
use function pulledbits\ActiveRecord\Test\createIndexResult;
use function pulledbits\ActiveRecord\Test\createMockResult;

class EntityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Entity
     */
    private $object;

    protected function setUp()
    {
        $this->schema = new class implements \pulledbits\ActiveRecord\Schema {
            private function convertResultSet(array $results) {
                return array_map(function(array $values) {
                    /**
                     * @var $this \pulledbits\ActiveRecord\Schema
                     */
                    $record = new SQL\Entity(new EntityType($this, 'MyTable'));
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
                return new SQL\Entity($this, $entityTypeIdentifier, [], [], []);
            }

            public function executeProcedure(string $procedureIdentifier, array $arguments): void
            {
            }

            public function makeRecord(string $entityTypeIdentifier): Record
            {
            }

            public function listEntityTypes(): EntityTypes
            {
                return [];
            }

            public function listForeignKeys(string $tableIdentifier): Result
            {
                switch ($tableIdentifier) {
                    case 'MyTable':
                        return createMockResult([
                            createConstraintResult('fk_othertable_role', 'role_id', 'OtherTable', 'id')
                        ]);

                    case 'MyTable2':
                        return createMockResult([
                            createConstraintResult('fk_othertable_role', 'role_id', 'OtherTable', 'id'),
                            createConstraintResult('fk_othertable_role', 'role2_id', 'OtherTable', 'id2')

                        ]);
                }
            }

            public function listIndexesForTable(string $tableIdentifier): Result
            {
                switch ($tableIdentifier) {
                    case 'MyTable':
                        return createMockResult([
                            createIndexResult($tableIdentifier, CONSTRAINT_KEY_PRIMARY, 'number')
                        ]);

                    case 'MyTable2':
                        return createMockResult([
                            createIndexResult($tableIdentifier, CONSTRAINT_KEY_PRIMARY, 'name')
                        ]);
                }
            }

            public function listColumnsForTable(string $tableIdentifier): Result
            {
                switch ($tableIdentifier) {
                    case 'MyTable':
                        return createMockResult([
                            createColumnResult('number', 'INT', true),
                            createColumnResult('role_id', 'INT', true),
                            createColumnResult('pole_id', 'INT', true)
                        ]);

                    case 'MyTable2':
                        return createMockResult([
                            createColumnResult('number', 'INT', true),
                            createColumnResult('name', 'INT', false),
                            createColumnResult('role_id', 'INT', true),
                            createColumnResult('role2_id', 'INT', true, true),
                            createColumnResult('pole_id', 'INT', true)
                        ]);
                }
            }
        };

        $values = [
            'number' => '1',
            'role_id' => '33',
            'pole_id' => '3654',
        ];
        $this->object = new Entity(new EntityType($this->schema, 'MyTable'));
        $this->object->contains($values);
    }

    public function test__get_When_ExistingProperty_Expect_Value()
    {
        $value = $this->object->number;
        $this->assertEquals('1', $value);
    }

    public function test__get_When_NotExistingProperty_Expect_Null()
    {
        $value = $this->object->doesnotexist;
        $this->assertNull($value);
    }

    public function test__set_When_ExistingProperty_Expect_ValueChanged()
    {
        $this->assertEquals('1', $this->object->number);
        $this->object->__set('number', '2');
        $this->assertEquals('2', $this->object->number);
    }

    public function test__set_When_NonExistingProperty_Expect_NoQueryValueUnchanged()
    {
        $this->object->contains(['numbero' => '1']);
        $this->assertEquals('1', $this->object->numbero);
        $this->object->__set('numbero', '2');
        $this->assertEquals('1', $this->object->numbero);
    }


    public function test__set_When_MissingRequiredProperty_Expect_NoChanges()
    {
        $object = new Entity(new EntityType($this->schema, 'MyTable2'));
        $object->contains([
            'number' => '1',
            'role_id' => '33',
            'pole_id' => '3654',
        ]);
        $this->assertEquals('1', $object->number);
        $object->__set('number', '2');
        $this->assertEquals('1', $object->number);
    }

    public function test__set_When_NulledRequiredProperty_Expect_NoChanges()
    {
        $object = new Entity(new EntityType($this->schema, 'MyTable2'));
        $object->contains([
            'number' => '1',
            'name' => null,
            'role_id' => '33',
            'pole_id' => '3654',
        ]);
        $this->assertEquals('1', $object->number);
        $object->__set('number', '2');
        $this->assertEquals('1', $object->number);
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
     * @expectedException \PHPUnit\Framework\Error\Error
     * @expectedExceptionMessageRegExp /^Required values are missing/
     */
    public function testCreate_When_RequiredValuesMissing_Expect_NoRecordsCreatedExpectError()
    {
        $object = new Entity(new EntityType($this->schema, 'MyTable2'));
        $object->contains([]);
        $this->assertEquals(0, $object->create());
        $object->contains(['name' => 'Test']);
        $this->assertEquals(1, $object->create());
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

    public function test__call_When_ExistingReferenceReferenceByCallWithAdditionalConditions_Expect_Value()
    {
        $record = $this->object->__call('referenceByFkOthertableRole', [["extra" => '6']]);
        $this->assertEquals('358', $record->id);
    }

    /**
     * @expectedException \PHPUnit\Framework\Error\Error
     * @expectedExceptionMessageRegExp /^Reference does not exist/
     */
    public function test__call_When_NonExistingReference_Expect_Value() {
        $this->object->__call('fetchByFkOthertableRoleWhichActuallyDoesNotExist', [["extra" => '5']]);
    }

    public function test__call_When_InvalidCallToMagicMethod_Expect_Null() {
        $this->assertNull($this->object->__call('InvalidCall', []));
    }
}
