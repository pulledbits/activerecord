<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 24-2-17
 * Time: 13:53
 */

namespace pulledbits\ActiveRecord\Source;


class GeneratorGeneratorFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testMakeGeneratorGeneratorFromDescription_When_TableDescription_Expect_EntityGeneratorGenerator() {
        $factory = new GeneratorGeneratorFactory();
        $object = $factory->makeGeneratorGenerator([
            'identifier' => ['id'],
            'requiredColumnIdentifiers' =>["a", "b", "c"],
            'references' => []
        ]);

        $this->assertEquals($factory->makeEntityGeneratorGenerator(['id'], ['a', 'b', 'c'], []), $object);
    }

}
