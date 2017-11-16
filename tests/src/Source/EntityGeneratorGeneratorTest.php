<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:30
 */

namespace pulledbits\ActiveRecord\Source;


class EntityGeneratorGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $base = '<?php return function(\pulledbits\ActiveRecord\Entity $record) {' . PHP_EOL .
    '    $record->identifiedBy(%s);' . PHP_EOL .
    '    $record->requires(%s);' . PHP_EOL .
    '    $record->references(%s);' . PHP_EOL .
    '    return $record;' . PHP_EOL .
    '};';

    private $baseTwoReferences = '<?php return function(\pulledbits\ActiveRecord\Entity $record) {' . PHP_EOL .
    '    $record->identifiedBy(%s);' . PHP_EOL .
    '    $record->requires(%s);' . PHP_EOL .
    '    $record->references(%s);' . PHP_EOL .
    '    $record->references(%s);' . PHP_EOL .
    '    return $record;' . PHP_EOL .
    '};';

    private $baseNoRequires = '<?php return function(\pulledbits\ActiveRecord\Entity $record) {' . PHP_EOL .
    '    $record->identifiedBy(%s);' . PHP_EOL .
    '    $record->references(%s);' . PHP_EOL .
    '    return $record;' . PHP_EOL .
    '};';

    private $baseNoReferences = '<?php return function(\pulledbits\ActiveRecord\Entity $record) {' . PHP_EOL .
    '    $record->identifiedBy(%s);' . PHP_EOL .
    '    $record->requires(%s);' . PHP_EOL .
    '    return $record;' . PHP_EOL .
    '};';

    /**
     * @var EntityGeneratorGenerator
     */
    private $object;

    protected function setUp()
    {
        $this->object = new EntityGeneratorGenerator(['id']);
    }

    public function testGenerate_When_ReferenceAddedLater_Expect_EntityGeneratorPHPCode() {
        $this->object->requires(["a", "b", "c"]);
        $this->object->references("FkRatingContactmoment", "rating", [
            'contactmoment_id' => 'id',
        ]);
        $this->assertEquals(sprintf($this->base, '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $this->object->generate());
    }

    public function testGenerate_When_ReferenceWithMultipleAttributes_Expect_EntityGeneratorPHPCode() {
        $this->object->requires(["a", "b", "c"]);
        $this->object->references("FkRatingContactmoment", "rating", [
            'contactmoment_id' => 'id',
            'foo_id' => 'bar_id'
        ]);
        $this->assertEquals(sprintf($this->base, '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id', 'foo_id' => 'bar_id']"), $this->object->generate());
    }

    public function testGenerate_When_TwoReferences_Expect_WithTwoReferencesWithoutEmptyLinePHPCode() {
        $this->object->requires(["a", "b", "c"]);
        $this->object->references("FkRatingContactmoment", "rating", [
            'contactmoment_id' => 'id',
        ]);
        $this->object->references("FkRatingContactmoment2", "rating2", [
            'contactmoment_id' => 'id',
        ]);
        $this->assertEquals(sprintf($this->baseTwoReferences, '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']", "'FkRatingContactmoment2', 'rating2', ['contactmoment_id' => 'id']"), $this->object->generate());
    }

    public function testGenerate_When_NoRequiredAttributeIdentifiers_Expect_WithoutRequiresCallPHPCode() {
        $this->object->references("FkRatingContactmoment", "rating", [
            'contactmoment_id' => 'id',
        ]);
        $this->assertEquals(sprintf($this->baseNoRequires, '[\'id\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $this->object->generate());
    }

    public function testGenerate_When_NoReferences_Expect_WithoutReferencesCallsPHPCode() {
        $this->object->requires(["a", "b", "c"]);
        $this->assertEquals(sprintf($this->baseNoReferences, '[\'id\']', '[\'a\', \'b\', \'c\']'), $this->object->generate());
    }
}
