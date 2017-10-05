<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 24-1-17
 * Time: 12:28
 */

namespace pulledbits\ActiveRecord;

use pulledbits\ActiveRecord\Source\Table;

class RecordFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeRecord_When_DefaultState_Expect_Record()
    {
//        file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'activiteit.php', '<?php
//return function(\pulledbits\ActiveRecord\Schema $schema, string $entityTypeIdentifier) {
//                    $record = new \pulledbits\ActiveRecord\Entity($schema, $entityTypeIdentifier, []);
//                    return $record;
//};');

        $schema = new class implements \pulledbits\ActiveRecord\Schema {

            public function read(string $tableIdentifier, array $columnIdentifiers, array $whereParameters): array
            {
                return [];
            }

            public function readFirst(string $tableIdentifier, array $columnIdentifiers, array $whereParameters): Record
            {
                return $this->read($tableIdentifier, $columnIdentifiers,$whereParameters)[0];
            }

            public function update(string $tableIdentifier, array $setParameters, array $whereParameters): int
            {
                return 0;
            }


            public function create(string $tableIdentifier, array $values): int
            {
                return 0;
            }

            public function delete(string $tableIdentifier, array $whereParameters): int
            {
                return 0;
            }

            public function initializeRecord(string $entityTypeIdentifier, array $values): Record
            {
                return new Entity($this, $entityTypeIdentifier, []);
            }

            public function executeProcedure(string $procedureIdentifier, array $arguments): void
            {

            }
        };

        $sourceSchema = new class implements Source\Schema {

            public function describeTable(Table $sourceTable, string $tableIdentifier): array
            {
                return [
                    'identifier' => [],
                    'requiredAttributeIdentifiers' => [],
                    'references' => []
                ];
            }

            public function describeTables(Table $sourceTable)
            {
                return [];
            }
        };

        $object = new RecordFactory($sourceSchema, sys_get_temp_dir());
        $record = $object->makeRecord($schema, 'activiteit');
        $record->contains(['status' => 'OK']);
        $this->assertEquals('OK', $record->status);
    }

}
