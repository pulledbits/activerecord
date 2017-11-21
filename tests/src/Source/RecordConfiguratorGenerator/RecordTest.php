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
        $this->object = new Record(['id']);
        $this->stream = createMockStreamInterface();
    }

    private function expectedCode(string $variantCode) {
        return '<?php namespace pulledbits\ActiveRecord;' . PHP_EOL .
            '    return new class($recordFactory) implements RecordConfigurator {' . PHP_EOL .
            '    private $recordFactory;' . PHP_EOL .
            '    public function __construct(RecordFactory $recordFactory) {' . PHP_EOL .
            '    $this->recordFactory = $recordFactory;' . PHP_EOL .
            '    }' . PHP_EOL .
            '    public function configure() : Record {' . PHP_EOL .
            '    $record = $this->recordFactory->makeRecord();' . PHP_EOL .
            $variantCode . PHP_EOL .
            '}};';

    }

    public function testGenerate_When_ReferenceAddedLater_Expect_EntityGeneratorPHPCode() {
        $this->object->requires(["a", "b", "c"]);
        $this->object->references("FkRatingContactmoment", "rating", [
            'contactmoment_id' => 'id',
        ]);

        $this->object->generateConfigurator($this->stream);
        $this->stream->seek(0);

        $this->assertEquals(sprintf($this->expectedCode($this->base), '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $this->stream->getContents());
    }

    public function testGenerate_When_ReferenceWithMultipleAttributes_Expect_EntityGeneratorPHPCode() {
        $this->object->requires(["a", "b", "c"]);
        $this->object->references("FkRatingContactmoment", "rating", [
            'contactmoment_id' => 'id',
            'foo_id' => 'bar_id'
        ]);

        $this->object->generateConfigurator($this->stream);
        $this->stream->seek(0);

        $this->assertEquals(sprintf($this->expectedCode($this->base), '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id', 'foo_id' => 'bar_id']"), $this->stream->getContents());
    }

    public function testGenerate_When_TwoReferences_Expect_WithTwoReferencesWithoutEmptyLinePHPCode() {
        $this->object->requires(["a", "b", "c"]);
        $this->object->references("FkRatingContactmoment", "rating", [
            'contactmoment_id' => 'id',
        ]);
        $this->object->references("FkRatingContactmoment2", "rating2", [
            'contactmoment_id' => 'id',
        ]);


        $this->object->generateConfigurator($this->stream);
        $this->stream->seek(0);

        $this->assertEquals(sprintf($this->expectedCode($this->baseTwoReferences), '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']", "'FkRatingContactmoment2', 'rating2', ['contactmoment_id' => 'id']"), $this->stream->getContents());
    }

    public function testGenerate_When_NoRequiredAttributeIdentifiers_Expect_WithoutRequiresCallPHPCode() {
        $this->object->references("FkRatingContactmoment", "rating", [
            'contactmoment_id' => 'id',
        ]);


        $this->object->generateConfigurator($this->stream);
        $this->stream->seek(0);


        $this->assertEquals(sprintf($this->expectedCode($this->baseNoRequires), '[\'id\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $this->stream->getContents());
    }

    public function testGenerate_When_NoReferences_Expect_WithoutReferencesCallsPHPCode() {
        $this->object->requires(["a", "b", "c"]);


        $this->object->generateConfigurator($this->stream);
        $this->stream->seek(0);

        $this->assertEquals(sprintf($this->expectedCode($this->baseNoReferences), '[\'id\']', '[\'a\', \'b\', \'c\']'), $this->stream->getContents());
    }
}
