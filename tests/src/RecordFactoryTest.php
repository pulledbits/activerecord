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

            public function executeRecordClassConfigurator(string $path, array $values): \ActiveRecord\Entity
            {
                return new \ActiveRecord\Entity($this, $values, [], $values);
            }

            public function select(array $columnIdentifiers, array $whereParameters)
            {}

            public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters)
            {}

            public function insert(array $values)
            {}

            public function update(array $setParameters, array $whereParameters)
            {}

            public function delete(array $whereParameters)
            {}
        };
        $object = new RecordFactory(sys_get_temp_dir());
        $record = $object->makeRecord($asset, ['status' => 'OK']);
        $this->assertEquals('OK', $record->status);
    }

}
