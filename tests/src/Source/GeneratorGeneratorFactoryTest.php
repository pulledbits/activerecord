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
    /**
     * @var GeneratorGeneratorFactory
     */
    private $object;

    protected function setUp()
    {
        $this->object = new GeneratorGeneratorFactory(new class implements Schema {
            public function describeTable(string $tableIdentifier): array
            {
                switch ($tableIdentifier) {
                    case 'base_table':
                        return [
                            'identifier' => ['id'],
                            'requiredAttributeIdentifiers' => ["a", "b", "c"],
                            'references' => []
                        ];
                    case 'view':
                        return [
                            'identifier' => ['id'],
                            'entityTypeIdentifier' => 'blabla',
                            'requiredAttributeIdentifiers' => ["a", "b", "c"],
                            'references' => []
                        ];

                }
            }

            public function describeTables()
            {
                // TODO: Implement describeTables() method.
            }
        });
    }

    public function testMakeGeneratorGeneratorFromDescription_When_TableDescription_Expect_EntityGeneratorGenerator() {
        
        $object = $this->object->makeGeneratorGenerator('base_table');

        $expectedObject = $this->object->makeEntityGeneratorGenerator(['id']);
        $expectedObject->requires(['a', 'b', 'c']);
        $this->assertEquals($expectedObject, $object);
    }

    public function testMakeGeneratorGeneratorFromDescription_When_WrappedEntityTypeIdentifier_Expect_WrappedEntityGeneratorGenerator() {
        
        $object = $this->object->makeGeneratorGenerator('view');

        $this->assertEquals($this->object->makeWrappedEntityGeneratorGenerator('blabla'), $object);
    }

}
