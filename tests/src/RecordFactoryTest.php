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
        file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'activiteit.php', '<?php
return function(\ActiveRecord\Schema $schema, string $entityTypeIdentifier, array $values) {
                    return new \ActiveRecord\Entity($schema, $entityTypeIdentifier, $values, [], $values);
};');

        $schema = new class implements \ActiveRecord\Schema {

            public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters): array
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
        $object = new RecordFactory(sys_get_temp_dir());
        $record = $object->makeRecord($schema, 'activiteit', ['status' => 'OK']);
        $this->assertEquals('OK', $record->status);
    }

}
