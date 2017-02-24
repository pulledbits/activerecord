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
    private $base = '<?php return function(\pulledbits\ActiveRecord\Schema $schema, string $entityTypeIdentifier) {' . PHP_EOL .
    '    $record = new \pulledbits\ActiveRecord\Entity($schema, $entityTypeIdentifier, %s);' . PHP_EOL .
    '    $record->requires(%s);' . PHP_EOL .
    '    $record->references(%s);' . PHP_EOL .
    '    return $record;' . PHP_EOL .
    '};';

    private $baseTwoReferences = '<?php return function(\pulledbits\ActiveRecord\Schema $schema, string $entityTypeIdentifier) {' . PHP_EOL .
    '    $record = new \pulledbits\ActiveRecord\Entity($schema, $entityTypeIdentifier, %s);' . PHP_EOL .
    '    $record->requires(%s);' . PHP_EOL .
    '    $record->references(%s);' . PHP_EOL .
    '    $record->references(%s);' . PHP_EOL .
    '    return $record;' . PHP_EOL .
    '};';

    private $baseNoRequires = '<?php return function(\pulledbits\ActiveRecord\Schema $schema, string $entityTypeIdentifier) {' . PHP_EOL .
    '    $record = new \pulledbits\ActiveRecord\Entity($schema, $entityTypeIdentifier, %s);' . PHP_EOL .
    '    $record->references(%s);' . PHP_EOL .
    '    return $record;' . PHP_EOL .
    '};';

    private $baseNoReferences = '<?php return function(\pulledbits\ActiveRecord\Schema $schema, string $entityTypeIdentifier) {' . PHP_EOL .
    '    $record = new \pulledbits\ActiveRecord\Entity($schema, $entityTypeIdentifier, %s);' . PHP_EOL .
    '    $record->requires(%s);' . PHP_EOL .
    '    return $record;' . PHP_EOL .
    '};';

    public function testGenerate_When_DefaultState_Expect_EntityGeneratorPHPCode() {
        $factory = new GeneratorGeneratorFactory();
        $object = $factory->makeEntityGeneratorGenerator(['id'], ["a", "b", "c"], ["FkRatingContactmoment" => [
            "table" => "rating",
            "where" => [
                'contactmoment_id' => 'id',
            ]
        ]]);
        $this->assertEquals(sprintf($this->base, '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $object->generate());
    }

    public function testGenerate_When_ReferenceWithMultipleAttributes_Expect_EntityGeneratorPHPCode() {
        $factory = new GeneratorGeneratorFactory();
        $object = $factory->makeEntityGeneratorGenerator(['id'], ["a", "b", "c"], ["FkRatingContactmoment" => [
            "table" => "rating",
            "where" => [
                'contactmoment_id' => 'id',
                'foo_id' => 'bar_id'
            ]
        ]]);
        $this->assertEquals(sprintf($this->base, '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id', 'foo_id' => 'bar_id']"), $object->generate());
    }

    public function testGenerate_When_TwoReferences_Expect_WithTwoReferencesWithoutEmptyLinePHPCode() {
        $factory = new GeneratorGeneratorFactory();
        $object = $factory->makeEntityGeneratorGenerator(['id'], ["a", "b", "c"], [
            "FkRatingContactmoment" => [
                "table" => "rating",
                "where" => [
                    'contactmoment_id' => 'id',
                ]
            ],
            "FkRatingContactmoment2" => [
                "table" => "rating2",
                "where" => [
                    'contactmoment_id' => 'id',
                ]
            ]
        ]);
        $this->assertEquals(sprintf($this->baseTwoReferences, '[\'id\']', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']", "'FkRatingContactmoment2', 'rating2', ['contactmoment_id' => 'id']"), $object->generate());
    }

    public function testGenerate_When_NoRequiredAttributeIdentifiers_Expect_WithoutRequiresCallPHPCode() {
        $factory = new GeneratorGeneratorFactory();
        $object = $factory->makeEntityGeneratorGenerator(['id'], [], ["FkRatingContactmoment" => [
            "table" => "rating",
            "where" => [
                'contactmoment_id' => 'id',
            ]
        ]]);
        $this->assertEquals(sprintf($this->baseNoRequires, '[\'id\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $object->generate());
    }

    public function testGenerate_When_NoReferences_Expect_WithoutReferencesCallsPHPCode() {
        $factory = new GeneratorGeneratorFactory();
        $object = $factory->makeEntityGeneratorGenerator(['id'], ["a", "b", "c"], []);
        $this->assertEquals(sprintf($this->baseNoReferences, '[\'id\']', '[\'a\', \'b\', \'c\']'), $object->generate());
    }
}
