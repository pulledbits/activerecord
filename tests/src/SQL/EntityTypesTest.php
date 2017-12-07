<?php

namespace pulledbits\ActiveRecord\SQL;


use pulledbits\ActiveRecord\Record;
use pulledbits\ActiveRecord\Result;
use function pulledbits\ActiveRecord\Test\createMockPDOStatement;
use function pulledbits\ActiveRecord\Test\createMockResult;
use function pulledbits\ActiveRecord\Test\createTableResult;
use function pulledbits\ActiveRecord\Test\createViewResult;

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

    public function testRetrieveTableDescription_When_EntityIsView_Expect_EmptyTableDescriptionForViewIdentifier()
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

        $object = new EntityTypes($schema, new \pulledbits\ActiveRecord\SQL\Query\Result(new Statement(createMockPDOStatement([
            createViewResult('MySchema', 'MyView')
        ]))));

        $this->assertEquals(new EntityType($schema, 'MyView'), $object->makeRecordType('MyView'));
    }
}
