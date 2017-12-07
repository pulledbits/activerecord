<?php

namespace pulledbits\ActiveRecord\SQL\MySQL;

use pulledbits\ActiveRecord\Result;
use pulledbits\ActiveRecord\SQL\Statement;
use function pulledbits\ActiveRecord\Test\createMockPDOStatement;
use function pulledbits\ActiveRecord\Test\createMockResult;
use function pulledbits\ActiveRecord\Test\createTableResult;
use function pulledbits\ActiveRecord\Test\createViewResult;

class EntityTypesTest extends \PHPUnit\Framework\TestCase
{
    private $schema;

    protected function setUp() {
        $this->schema = new class extends Schema {
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
    }

    public function testRetrieveTableDescription_When_EntityNotExists_Expect_EmptyTableDescription()
    {
        $object = new EntityTypes($this->schema, new Query\Result(new Statement(createMockPDOStatement([]))));

        $this->assertEquals(new EntityType($this->schema, 'NotExisting'), $object->makeRecordType('NotExisting'));
    }

    public function testRetrieveTableDescription_When_EntityIsView_Expect_EmptyTableDescriptionForViewIdentifier()
    {
        $object = new EntityTypes($this->schema, new Query\Result(new Statement(createMockPDOStatement([
            createViewResult('MySchema', 'MyView')
        ]))));

        $this->assertEquals(new EntityType($this->schema, 'MyView'), $object->makeRecordType('MyView'));
    }

    public function testRetrieveTableDescription_When_EntityIsViewWrappedAroundOtherTable_Expect_EntityTypeForWrappedTable()
    {
        $object = new EntityTypes($this->schema, new Query\Result(new Statement(createMockPDOStatement([
            createTableResult('MySchema', 'MyTable'),
            createViewResult('MySchema', 'MyTable_MyView')
        ]))));

        $this->assertEquals(new EntityType($this->schema, 'MyTable'), $object->makeRecordType('MyTable_MyView'));
    }
}
