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
            'requiredAttributeIdentifiers' =>["a", "b", "c"],
            'references' => []
        ]);

        $this->assertEquals($factory->makeEntityGeneratorGenerator(['id'], ['a', 'b', 'c'], []), $object);
    }

    public function testMakeGeneratorGeneratorFromDescription_When_WrappedEntityTypeIdentifier_Expect_WrappedEntityGeneratorGenerator() {
        $factory = new GeneratorGeneratorFactory();
        $object = $factory->makeGeneratorGenerator([
            'identifier' => ['id'],
            'entityTypeIdentifier' => 'blabla',
            'requiredAttributeIdentifiers' =>["a", "b", "c"],
            'references' => []
        ]);

        $this->assertEquals($factory->makeWrappedEntityGeneratorGenerator('blabla'), $object);
    }
}
