<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:30
 */

namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

use pulledbits\ActiveRecord\Record;
use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\RecordType;
use pulledbits\ActiveRecord\Schema;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;
use pulledbits\ActiveRecord\SQL\EntityType;

class WrappedEntityTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->recordFactory = new EntityType(new class implements Schema {
            public function makeRecordType(string $entityTypeIdentifier): RecordType
            {
                // TODO: Implement makeRecordType() method.
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

    private function makeWrappedEntityConfiguratorGenerator() {
        return new class implements RecordConfiguratorGenerator, RecordConfigurator {
            public function generateConfigurator(): RecordConfigurator
            {
                return $this;
            }

            public function configure(): Record
            {
            }
        };
    }

    public function testGenerate_When_DefaultState_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $expectedConfigurator = $this->makeWrappedEntityConfiguratorGenerator();

        $object = new WrappedEntity($expectedConfigurator);

        $configurator = $object->generateConfigurator($this->recordFactory);

        $this->assertEquals($expectedConfigurator, $configurator);
    }
    public function testGenerate_When_OtherTable_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $configurator = $this->makeWrappedEntityConfiguratorGenerator();

        $object = new WrappedEntity($configurator);

        $object->generateConfigurator($this->recordFactory);

        $this->assertEquals($configurator, $configurator);
    }
}
