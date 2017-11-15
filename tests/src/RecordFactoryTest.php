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
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ActiveRecordTest';

        $schema = new class implements \pulledbits\ActiveRecord\Schema {

            public function read(string $tableIdentifier, array $columnIdentifiers, array $whereParameters): array
            {
                return [];
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

        $sourceSchema = \pulledbits\ActiveRecord\Test\createMockSchema([
            'activiteit' => []
        ]);

        $object = new RecordFactory(new \pulledbits\ActiveRecord\Configurator($sourceSchema, $directory));
        $record = $object->makeRecord($schema, 'activiteit');
        $record->contains(['status' => 'OK']);
        $this->assertEquals('OK', $record->status);


        unlink($directory . DIRECTORY_SEPARATOR . 'activiteit.php');
        rmdir($directory);
    }

    public function testMakeRecord_When_View_Expect_RecordWrappedAroundOtherEntity()
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ActiveRecordViewTest';

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

        $sourceSchema = \pulledbits\ActiveRecord\Test\createMockSchema([
            'activiteit_vandaag' => 'activiteit',
            'activiteit' => []
        ]);

        $object = new RecordFactory(new \pulledbits\ActiveRecord\Configurator($sourceSchema, $directory));
        $record = $object->makeRecord($schema, 'activiteit_vandaag');
        $record->contains(['status' => 'OK']);
        $this->assertEquals('OK', $record->status);

        unlink($directory . DIRECTORY_SEPARATOR . 'activiteit_vandaag.php');
        unlink($directory . DIRECTORY_SEPARATOR . 'activiteit.php');
        rmdir($directory);
    }
}
