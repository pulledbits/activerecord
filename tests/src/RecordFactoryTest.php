<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 24-1-17
 * Time: 12:28
 */

namespace ActiveRecord;


use ActiveRecord\Schema\Asset;

class RecordFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeRecord_When_DefaultState_Expect_Record()
    {
        file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'activiteit.php', '<?php
return function(\ActiveRecord\Schema\Asset $asset, array $values) {
    return new \ActiveRecord\Record($asset, $values, [], $values);
};');
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
        $object = new RecordFactory(sys_get_temp_dir());
        $record = $object->makeRecord(new Asset('activiteit', $schema), ['status' => 'OK']);
        $this->assertEquals('OK', $record->status);
    }

}
