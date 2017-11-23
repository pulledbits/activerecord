<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:30
 */

namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;


use pulledbits\ActiveRecord\Schema;
use pulledbits\ActiveRecord\Source\TableDescription;
use pulledbits\ActiveRecord\SQL\EntityFactory;
use function pulledbits\ActiveRecord\Test\createMockStreamInterface;

class RecordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Record
     */
    private $object;
    private $recordFactory;

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

    private function expectedConfigurator() {
        return new \pulledbits\ActiveRecord\RecordConfigurator($this->recordFactory);
    }

    private function expectedConfiguratorBase(array $identifiedBy, array $requires, array $reference) {
        $configurator = $this->expectedConfigurator();
        $configurator->identifiedBy($identifiedBy);
        $configurator->requires($requires);
        $configurator->references($reference[0], $reference[1], $reference[2]);
        return $configurator;
    }
    private function expectedConfiguratorBaseTwoReferences(array $identifiedBy, array $requires, array $reference1, array $reference2) {
        $configurator = $this->expectedConfigurator();
        $configurator->identifiedBy($identifiedBy);
        $configurator->requires($requires);
        $configurator->references($reference1[0], $reference1[1], $reference1[2]);
        $configurator->references($reference2[0], $reference2[1], $reference2[2]);
        return $configurator;
    }
    private function expectedConfiguratorBaseNoRequires(array $identifiedBy, array $reference) {
        $configurator = $this->expectedConfigurator();
        $configurator->identifiedBy($identifiedBy);
        $configurator->references($reference[0], $reference[1], $reference[2]);
        return $configurator;
    }
    private function expectedConfiguratorBaseNoReferences(array $identifiedBy, array $requires) {
        $configurator = $this->expectedConfigurator();
        $configurator->identifiedBy($identifiedBy);
        $configurator->requires($requires);
        return $configurator;
    }

    private function createTableDescription(array $entityIdentifier, array $requiredAttributes, array $references) {
        return new TableDescription($entityIdentifier, $requiredAttributes, $references);
    }

    public function testGenerate_When_ReferenceAddedLater_Expect_EntityGeneratorPHPCode() {
        $this->object = new Record($this->createTableDescription(['id'], ["a", "b", "c"], [
            "FkRatingContactmoment" => ['table' => "rating", 'where' => [
                'contactmoment_id' => 'id',
            ]]
        ]));

        $configurator = $this->object->generateConfigurator($this->recordFactory);

        $this->assertEquals($this->expectedConfiguratorBase(['id'], ['a', 'b', 'c'], ['FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']]), $configurator);
    }

    public function testGenerate_When_ReferenceWithMultipleAttributes_Expect_EntityGeneratorPHPCode() {
        $this->object = new Record($this->createTableDescription(['id'], ["a", "b", "c"], [
            "FkRatingContactmoment" => ['table' => "rating", 'where' => [
                'contactmoment_id' => 'id',
                'foo_id' => 'bar_id'
            ]]
        ]));

        $configurator = $this->object->generateConfigurator($this->recordFactory);

        $this->assertEquals($this->expectedConfiguratorBase(['id'], ['a', 'b', 'c'], ['FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id', 'foo_id' => 'bar_id']]), $configurator);
    }

    public function testGenerate_When_TwoReferences_Expect_WithTwoReferencesWithoutEmptyLinePHPCode() {
        $this->object = new Record($this->createTableDescription(['id'], ["a", "b", "c"], [
            "FkRatingContactmoment" => ['table' => "rating", 'where' => [
                'contactmoment_id' => 'id'
            ]],
            "FkRatingContactmoment2" => ['table' => "rating2", 'where' => [
                'contactmoment_id' => 'id'
            ]]
        ]));

        $configurator = $this->object->generateConfigurator($this->recordFactory);

        $this->assertEquals($this->expectedConfiguratorBaseTwoReferences(['id'], ['a', 'b', 'c'], ['FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']], ['FkRatingContactmoment2', 'rating2', ['contactmoment_id' => 'id']]), $configurator);
    }

    public function testGenerate_When_NoRequiredAttributeIdentifiers_Expect_WithoutRequiresCallPHPCode() {
        $this->object = new Record($this->createTableDescription(['id'], [], [
            "FkRatingContactmoment" => ['table' => "rating", 'where' => [
                'contactmoment_id' => 'id',
            ]]
        ]));

        $configurator = $this->object->generateConfigurator($this->recordFactory);


        $this->assertEquals($this->expectedConfiguratorBaseNoRequires(['id'], ['FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']]), $configurator);
    }

    public function testGenerate_When_NoReferences_Expect_WithoutReferencesCallsPHPCode() {
        $this->object = new Record($this->createTableDescription(['id'], ["a", "b", "c"], []));

        $configurator = $this->object->generateConfigurator($this->recordFactory);

        $this->assertEquals($this->expectedConfiguratorBaseNoReferences(['id'], ['a', 'b', 'c']), $configurator);
    }
}
