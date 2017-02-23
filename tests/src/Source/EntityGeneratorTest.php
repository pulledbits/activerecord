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

    public function testGenerate_When_DefaultState_Expect_EntityGeneratorPHPCode() {
        $object = new EntityGenerator('MyTable', ["a", "b", "c"], ["FkRatingContactmoment" => [
            "table" => "rating",
            "where" => [
                'contactmoment_id' => 'id',
            ]
        ]]);
        $this->assertEquals(sprintf($this->base, '\'MyTable\'', '[\'a\', \'b\', \'c\']', "'FkRatingContactmoment', 'rating', ['contactmoment_id' => 'id']"), $object->generate());
    }
}
