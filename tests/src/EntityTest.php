<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 25-1-17
 * Time: 15:50
 */

namespace ActiveRecord;


class EntityTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $asset = new class implements \ActiveRecord\Schema\EntityType{
            public function executeEntityConfigurator(string $path, array $values): \ActiveRecord\Entity
            {}

            public function select(array $columnIdentifiers, array $whereParameters) : array
            {}

            public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters) : array
            {}

            public function insert(array $values) : int
            { }

            public function update(array $setParameters, array $whereParameters) : int
            {
                if ($setParameters === ['number' => '2'] && $whereParameters === ['number' => '1']) {
                    return 1;
                }
                return 0;
            }

            public function delete(array $whereParameters) : int
            {}
        };
        $primaryKey = [];
        $references = [];
        $values = [
            'number' => '1'
        ];
        $this->object = new Entity($asset, $primaryKey, $references, $values);
    }

    public function test__get_When_ExistingProperty_Expect_Value()
    {
        $value = $this->object->number;
        $this->assertEquals('1', $value);
    }

    public function test__set_When_ExistingProperty_Expect_ValueChanged()
    {
        $this->assertEquals('1', $this->object->number);
        $this->object->number = '2';
        $this->assertEquals('2', $this->object->number);
    }

}
