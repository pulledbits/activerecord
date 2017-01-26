<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 24-1-17
 * Time: 12:28
 */

namespace ActiveRecord;


use ActiveRecord\Schema\EntityType;

class RecordFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeRecord_When_DefaultState_Expect_Record()
    {
        $asset = new class implements EntityType {

            public function executeEntityConfigurator(string $path, array $values): \ActiveRecord\Entity
            {
                $schema = new class implements \ActiveRecord\Schema {

                    public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters, \ActiveRecord\Schema\EntityType $entityType): array
                    {
                        // TODO: Implement selectFrom() method.
                    }

                    public function updateWhere(string $tableIdentifier, array $setParameters, array $whereParameters): int
                    {
                        // TODO: Implement updateWhere() method.
                    }

                    public function insertValues(string $tableIdentifier, array $values): int
                    {
                        // TODO: Implement insertValues() method.
                    }

                    public function deleteFrom(string $tableIdentifier, array $whereParameters): int
                    {
                        // TODO: Implement deleteFrom() method.
                    }
                };

                return new \ActiveRecord\Entity($this, $schema, 'MyTable', $values, [], $values);
            }

            public function select(array $columnIdentifiers, array $whereParameters) : array
            {}

            public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters) : array
            {}

            public function insert(array $values) : int
            {}

            public function update(array $setParameters, array $whereParameters) : int
            {}

            public function delete(array $whereParameters) : int
            {}
        };
        $object = new RecordFactory(sys_get_temp_dir());
        $record = $object->makeRecord($asset, ['status' => 'OK']);
        $this->assertEquals('OK', $record->status);
    }

}
