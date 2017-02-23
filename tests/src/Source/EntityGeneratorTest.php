<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:30
 */

namespace pulledbits\ActiveRecord\Source;


class EntityGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $base = '<?php return function(\pulledbits\ActiveRecord\Schema $schema, string $entityTypeIdentifier) {
    $record = new \pulledbits\ActiveRecord\Entity($schema, $entityTypeIdentifier, %s);
    $record->requires(%s);
    $record->references(%s);
    return $record;
};';

    private $baseNoRequires = '<?php return function(\pulledbits\ActiveRecord\Schema $schema, string $entityTypeIdentifier) {
    $record = new \pulledbits\ActiveRecord\Entity($schema, $entityTypeIdentifier, %s);
    $record->references(%s);
    return $record;
};';

    private $baseNoReferences = '<?php return function(\pulledbits\ActiveRecord\Schema $schema, string $entityTypeIdentifier) {
    $record = new \pulledbits\ActiveRecord\Entity($schema, $entityTypeIdentifier, %s);
    $record->requires(%s);
    return $record;
};';

    public function testGenerate_When_DefaultState_Expect_EntityGeneratorPHPCode() {
        $object = new EntityGenerator('MyTable', ["a", "b", "c"], ["FkRatingContactmoment" => [
            "table" => "rating",
            "where" => [
                'contactmoment_id' => 'id',
            ]
        ]]);
        $this->assertEquals(sprintf($this->base, '\'MyTable\'', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $object->generate());
    }

    public function testGenerate_When_NoRequiredAttributeIdentifiers_Expect_EntityGeneratorWithoutRequiresCallPHPCode() {
        $object = new EntityGenerator('MyTable', [], ["FkRatingContactmoment" => [
            "table" => "rating",
            "where" => [
                'contactmoment_id' => 'id',
            ]
        ]]);
        $this->assertEquals(sprintf($this->baseNoRequires, '\'MyTable\'', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $object->generate());
    }

    public function testGenerate_When_NoReferences_Expect_EntityGeneratorWithoutReferencesCallsPHPCode() {
        $object = new EntityGenerator('MyTable', ["a", "b", "c"], []);
        $this->assertEquals(sprintf($this->baseNoReferences, '\'MyTable\'', '[\'a\', \'b\', \'c\']'), $object->generate());
    }
}
