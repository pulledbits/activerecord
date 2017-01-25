<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 25-1-17
 * Time: 15:50
 */

namespace ActiveRecord;


class RecordTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $schema = new class implements Schema {
            public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters, \Closure $recordConverter): array
            {}

            public function updateWhere(string $tableIdentifier, array $setParameters, array $whereParameters): int
            {}

            public function insertValues(string $tableIdentifier, array $values): int
            {}

            public function deleteFrom(string $tableIdentifier, array $whereParameters): int
            {}
        };
        $asset = new \ActiveRecord\Schema\Asset('activeit', $schema);
        $primaryKey = [];
        $references = [];
        $values = [
            'number' => '1'
        ];
        $this->object = new Record($asset, $primaryKey, $references, $values);
    }

    public function test__get_When_ExistingProperty_Expect_Value()
    {
        $value = $this->object->number;
        $this->assertEquals('1', $value);
    }


}
