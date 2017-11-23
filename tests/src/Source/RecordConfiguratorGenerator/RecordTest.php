<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:30
 */

namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;


use pulledbits\ActiveRecord\Source\TableDescription;
use function pulledbits\ActiveRecord\Test\createMockStreamInterface;

class RecordTest extends \PHPUnit_Framework_TestCase
{
    private $base = '$configurator->identifiedBy(%s);' . PHP_EOL .
    '$configurator->requires(%s);' . PHP_EOL .
    '$configurator->references(%s);';

    private $baseTwoReferences = '$configurator->identifiedBy(%s);' . PHP_EOL .
    '$configurator->requires(%s);' . PHP_EOL .
    '$configurator->references(%s);' . PHP_EOL .
    '$configurator->references(%s);';

    private $baseNoRequires = '$configurator->identifiedBy(%s);' . PHP_EOL .
    '$configurator->references(%s);';

    private $baseNoReferences = '$configurator->identifiedBy(%s);' . PHP_EOL .
    '$configurator->requires(%s);';

    /**
     * @var Record
     */
    private $object;
    private $stream;

    protected function setUp()
    {
        $this->stream = createMockStreamInterface();
    }

    private function expectedCode(string $variantCode) {
        return PHP_EOL .
            '$configurator = new \\pulledbits\\ActiveRecord\\RecordConfigurator($recordFactory);' . PHP_EOL .
            $variantCode . PHP_EOL .
            'return $configurator;';
    }

    private function expectedCodeBase(string $identifiedBy, string $requires, string $reference) {
        return sprintf($this->expectedCode($this->base), $identifiedBy, $requires, $reference);
    }
    private function expectedCodeBaseTwoReferences(string $identifiedBy, string $requires, string $reference1, string $reference2) {
        return sprintf($this->expectedCode($this->baseTwoReferences), $identifiedBy, $requires, $reference1, $reference2);
    }
    private function expectedCodeBaseNoRequires(string $identifiedBy, string $reference) {
        return sprintf($this->expectedCode($this->baseNoRequires), $identifiedBy, $reference);
    }
    private function expectedCodeBaseNoReferences(string $identifiedBy, string $requires) {
        return sprintf($this->expectedCode($this->baseNoReferences), $identifiedBy, $requires);
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

        $this->object->generateConfigurator($this->stream);
        $this->stream->seek(0);

        $this->assertEquals($this->expectedCodeBase('[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $this->stream->getContents());
    }

    public function testGenerate_When_ReferenceWithMultipleAttributes_Expect_EntityGeneratorPHPCode() {
        $this->object = new Record($this->createTableDescription(['id'], ["a", "b", "c"], [
            "FkRatingContactmoment" => ['table' => "rating", 'where' => [
                'contactmoment_id' => 'id',
                'foo_id' => 'bar_id'
            ]]
        ]));

        $this->object->generateConfigurator($this->stream);
        $this->stream->seek(0);

        $this->assertEquals($this->expectedCodeBase('[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id', 'foo_id' => 'bar_id']"), $this->stream->getContents());
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

        $this->object->generateConfigurator($this->stream);
        $this->stream->seek(0);

        $this->assertEquals($this->expectedCodeBaseTwoReferences('[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']", "'FkRatingContactmoment2', 'rating2', ['contactmoment_id' => 'id']"), $this->stream->getContents());
    }

    public function testGenerate_When_NoRequiredAttributeIdentifiers_Expect_WithoutRequiresCallPHPCode() {
        $this->object = new Record($this->createTableDescription(['id'], [], [
            "FkRatingContactmoment" => ['table' => "rating", 'where' => [
                'contactmoment_id' => 'id',
            ]]
        ]));

        $this->object->generateConfigurator($this->stream);
        $this->stream->seek(0);


        $this->assertEquals($this->expectedCodeBaseNoRequires('[\'id\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $this->stream->getContents());
    }

    public function testGenerate_When_NoReferences_Expect_WithoutReferencesCallsPHPCode() {
        $this->object = new Record($this->createTableDescription(['id'], ["a", "b", "c"], []));

        $this->object->generateConfigurator($this->stream);
        $this->stream->seek(0);

        $this->assertEquals($this->expectedCodeBaseNoReferences('[\'id\']', '[\'a\', \'b\', \'c\']'), $this->stream->getContents());
    }
}
