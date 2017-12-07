<?php

namespace pulledbits\ActiveRecord\SQL;


use pulledbits\ActiveRecord\Record;
use pulledbits\ActiveRecord\Result;
use function pulledbits\ActiveRecord\Test\createMockPDOStatement;
use function pulledbits\ActiveRecord\Test\createMockResult;

class EntityTypesTest extends \PHPUnit\Framework\TestCase
{

    public function testRetrieveTableDescription_When_EntityNotExists_Expect_EmptyTableDescription()
    {
        $schema = new class extends Schema {
            public function __construct() {

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

        $object = new EntityTypes($schema, new \pulledbits\ActiveRecord\SQL\Query\Result(new Statement(createMockPDOStatement([]))));

        $this->assertEquals(new EntityType($schema, 'NotExisting'), $object->makeRecordType('NotExisting'));
    }
}
