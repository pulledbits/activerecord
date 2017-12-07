<?php

namespace pulledbits\ActiveRecord;


use pulledbits\ActiveRecord\SQL\EntityType;
use function pulledbits\ActiveRecord\Test\createMockResult;

class EntityTypesTest extends \PHPUnit\Framework\TestCase
{

    public function testRetrieveTableDescription_When_EntityNotExists_Expect_EmptyTableDescription()
    {
        $schema = new class implements Schema {

            public function makeRecord(string $entityTypeIdentifier): Record
            {
            }

            public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions): array
            {
            }

            public function update(string $entityTypeIdentifier, array $values, array $conditions): int
            {
            }

            public function create(string $entityTypeIdentifier, array $values): int
            {
            }

            public function delete(string $entityTypeIdentifier, array $conditions): int
            {
            }

            public function executeProcedure(string $procedureIdentifier, array $arguments): void
            {
            }

            public function listForeignKeys(string $tableIdentifier): Result
            {
                return createMockResult([]);
            }

            public function listIndexesForTable(string $tableIdentifier): Result
            {
                return createMockResult([]);
            }

            public function listColumnsForTable(string $tableIdentifier): Result
            {
                return createMockResult([]);
            }
        };
        $result = new class implements Result {

            public function fetchAll(): array
            {
                return [];
            }

            public function count()
            {
                return 0;
            }
        };

        $object = new EntityTypes($schema, $result);

        $this->assertEquals(new EntityType($schema, 'NotExisting'), $object->makeRecordType('NotExisting'));
    }
}
