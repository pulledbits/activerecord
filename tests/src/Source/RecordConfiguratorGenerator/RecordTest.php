<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:30
 */

namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;


use function pulledbits\ActiveRecord\Test\createMockStreamInterface;

class RecordTest extends \PHPUnit_Framework_TestCase
{
    private $base = '    $record->identifiedBy(%s);' . PHP_EOL .
    '    $record->requires(%s);' . PHP_EOL .
    '    $record->references(%s);' . PHP_EOL .
    '    return $record;';

    private $baseTwoReferences = '    $record->identifiedBy(%s);' . PHP_EOL .
    '    $record->requires(%s);' . PHP_EOL .
    '    $record->references(%s);' . PHP_EOL .
    '    $record->references(%s);' . PHP_EOL .
    '    return $record;';

    private $baseNoRequires = '    $record->identifiedBy(%s);' . PHP_EOL .
    '    $record->references(%s);' . PHP_EOL .
    '    return $record;';

    private $baseNoReferences = '    $record->identifiedBy(%s);' . PHP_EOL .
    '    $record->requires(%s);' . PHP_EOL .
    '    return $record;';

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
            '    return new class($recordFactory) implements \\pulledbits\\ActiveRecord\\RecordConfigurator {' . PHP_EOL .
            '    private $recordFactory;' . PHP_EOL .
            '    public function __construct(\\pulledbits\\ActiveRecord\\RecordFactory $recordFactory) {' . PHP_EOL .
            '    $this->recordFactory = $recordFactory;' . PHP_EOL .
            '    }' . PHP_EOL .
            '    public function configure() : \\pulledbits\\ActiveRecord\\Record {' . PHP_EOL .
            '    $record = $this->recordFactory->makeRecord();' . PHP_EOL .
            $variantCode . PHP_EOL .
            '}};';

    }

    private function createTableDescription(array $entityIdentifier, array $requiredAttributes, array $references) {
        return [
            'identifier' => $entityIdentifier,
            'requiredAttributeIdentifiers' => $requiredAttributes,
            'references' => $references
        ];
    }

    public function testGenerate_When_ReferenceAddedLater_Expect_EntityGeneratorPHPCode() {
        $this->object = new Record($this->createTableDescription(['id'], ["a", "b", "c"], [
            "FkRatingContactmoment" => ['table' => "rating", 'where' => [
                'contactmoment_id' => 'id',
            ]]
        ]));

        $this->object->generateConfigurator($this->stream);
        $this->stream->seek(0);

        $this->assertEquals(sprintf($this->expectedCode($this->base), '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $this->stream->getContents());
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

        $this->assertEquals(sprintf($this->expectedCode($this->base), '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id', 'foo_id' => 'bar_id']"), $this->stream->getContents());
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

        $this->assertEquals(sprintf($this->expectedCode($this->baseTwoReferences), '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']", "'FkRatingContactmoment2', 'rating2', ['contactmoment_id' => 'id']"), $this->stream->getContents());
    }

    public function testGenerate_When_NoRequiredAttributeIdentifiers_Expect_WithoutRequiresCallPHPCode() {
        $this->object = new Record($this->createTableDescription(['id'], [], [
            "FkRatingContactmoment" => ['table' => "rating", 'where' => [
                'contactmoment_id' => 'id',
            ]]
        ]));

        $this->object->generateConfigurator($this->stream);
        $this->stream->seek(0);


        $this->assertEquals(sprintf($this->expectedCode($this->baseNoRequires), '[\'id\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $this->stream->getContents());
    }

    public function testGenerate_When_NoReferences_Expect_WithoutReferencesCallsPHPCode() {
        $this->object = new Record($this->createTableDescription(['id'], ["a", "b", "c"], []));

        $this->object->generateConfigurator($this->stream);
        $this->stream->seek(0);

        $this->assertEquals(sprintf($this->expectedCode($this->baseNoReferences), '[\'id\']', '[\'a\', \'b\', \'c\']'), $this->stream->getContents());
    }
}
