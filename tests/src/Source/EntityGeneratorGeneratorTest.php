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
     * @var GeneratorGeneratorFactory
     */
    private $object;

    protected function setUp()
    {
        $this->object = new GeneratorGeneratorFactory(new class implements Schema {
            public function describeTable(string $tableIdentifier): array
            {
                // TODO: Implement describeTable() method.
            }

            public function describeTables()
            {
                // TODO: Implement describeTables() method.
            }
        });
    }

    public function testGenerate_When_ReferenceAddedLater_Expect_EntityGeneratorPHPCode() {
        $object = $this->object->makeEntityGeneratorGenerator(['id']);
        $object->requires(["a", "b", "c"]);
        $object->references("FkRatingContactmoment", "rating", [
            'contactmoment_id' => 'id',
        ]);
        $this->assertEquals(sprintf($this->base, '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $object->generate());
    }

    public function testGenerate_When_ReferenceWithMultipleAttributes_Expect_EntityGeneratorPHPCode() {
        $object = $this->object->makeEntityGeneratorGenerator(['id']);
        $object->requires(["a", "b", "c"]);
        $object->references("FkRatingContactmoment", "rating", [
            'contactmoment_id' => 'id',
            'foo_id' => 'bar_id'
        ]);
        $this->assertEquals(sprintf($this->base, '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id', 'foo_id' => 'bar_id']"), $object->generate());
    }

    public function testGenerate_When_TwoReferences_Expect_WithTwoReferencesWithoutEmptyLinePHPCode() {
        $object = $this->object->makeEntityGeneratorGenerator(['id']);
        $object->requires(["a", "b", "c"]);
        $object->references("FkRatingContactmoment", "rating", [
            'contactmoment_id' => 'id',
        ]);
        $object->references("FkRatingContactmoment2", "rating2", [
            'contactmoment_id' => 'id',
        ]);
        $this->assertEquals(sprintf($this->baseTwoReferences, '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']", "'FkRatingContactmoment2', 'rating2', ['contactmoment_id' => 'id']"), $object->generate());
    }

    public function testGenerate_When_NoRequiredAttributeIdentifiers_Expect_WithoutRequiresCallPHPCode() {
        $object = $this->object->makeEntityGeneratorGenerator(['id']);
        $object->references("FkRatingContactmoment", "rating", [
            'contactmoment_id' => 'id',
        ]);
        $this->assertEquals(sprintf($this->baseNoRequires, '[\'id\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $object->generate());
    }

    public function testGenerate_When_NoReferences_Expect_WithoutReferencesCallsPHPCode() {
        $object = $this->object->makeEntityGeneratorGenerator(['id']);
        $object->requires(["a", "b", "c"]);
        $this->assertEquals(sprintf($this->baseNoReferences, '[\'id\']', '[\'a\', \'b\', \'c\']'), $object->generate());
    }
}
