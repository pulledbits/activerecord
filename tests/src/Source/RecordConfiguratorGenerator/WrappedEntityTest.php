<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:30
 */

namespace pulledbits\ActiveRecord\Source\RecordConfigurator;

use pulledbits\ActiveRecord\Record;
use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\RecordType;
use pulledbits\ActiveRecord\Schema;
use pulledbits\ActiveRecord\Source\RecordConfigurator;
use pulledbits\ActiveRecord\SQL\EntityType;

class WrappedEntityTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->recordFactory = new EntityType(new class implements Schema {
            public function makeRecordType(string $entityTypeIdentifier): RecordType
            {
            }

            public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions): array
            {
            }

            public function update(string $entityTypeIdentifier, array $values, array $conditions): int
            {
            }

            public function create(string $entityTypeIdentifier, array $values): int
            {
            }

            public function delete(string $entityTypeIdentifier, array $conditions): int
            {
            }

            public function executeProcedure(string $procedureIdentifier, array $arguments): void
            {
            }
        }, 'RecordTest');
    }

    private function makeWrappedEntityConfiguratorGenerator(Record $record) {
        return new class($record) implements RecordConfigurator {
            private $record;
            public function __construct(Record $record)
            {
                $this->record = $record;
            }

            public function configure(): Record
            {
                return $this->record;
            }
        };
    }

    public function testGenerate_When_DefaultState_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $expectedRecordConfigurator = $this->makeWrappedEntityConfiguratorGenerator($this->recordFactory->makeRecord());

        $object = new WrappedEntity($expectedRecordConfigurator);

        $record = $object->configure();

        $this->assertEquals($expectedRecordConfigurator->configure(), $record);
    }
}
