<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:30
 */

namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\RecordFactory;
use pulledbits\ActiveRecord\Schema;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;
use pulledbits\ActiveRecord\SQL\EntityFactory;

class WrappedEntityTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->recordFactory = new EntityFactory(new class implements Schema {
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

    private function makeWrappedEntityConfiguratorGenerator(RecordConfigurator $configurator) {
        return new class($configurator) implements RecordConfiguratorGenerator {
            private $configurator;
            public function __construct(RecordConfigurator $configurator)
            {
                $this->configurator = $configurator;
            }

            public function generateConfigurator(RecordFactory $recordFactory): RecordConfigurator
            {
                return $this->configurator;
            }
        };
    }

    public function testGenerate_When_DefaultState_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $expectedConfigurator = new RecordConfigurator($this->recordFactory);

        $object = new WrappedEntity($this->makeWrappedEntityConfiguratorGenerator($expectedConfigurator));

        $configurator = $object->generateConfigurator($this->recordFactory);

        $this->assertEquals($expectedConfigurator, $configurator);
    }
    public function testGenerate_When_OtherTable_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $configurator = new RecordConfigurator($this->recordFactory);

        $object = new WrappedEntity($this->makeWrappedEntityConfiguratorGenerator($configurator));

        $object->generateConfigurator($this->recordFactory);

        $this->assertEquals($configurator, $configurator);
    }
}
