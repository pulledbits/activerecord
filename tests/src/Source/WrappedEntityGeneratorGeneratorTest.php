<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:30
 */

namespace pulledbits\ActiveRecord\Source;


class WrappedEntityGeneratorGeneratorTest extends \PHPUnit_Framework_TestCase
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
                // TODO: Implement describeTable() method.
            }

            public function describeTables()
            {
                // TODO: Implement describeTables() method.
            }
        });
    }

    public function testGenerate_When_DefaultState_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $object = $this->object->makeWrappedEntityGeneratorGenerator('MyTable');
        $this->assertEquals('<?php return $this->generate("MyTable");', $object->generate());
    }
    public function testGenerate_When_OtherTable_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $object = $this->object->makeWrappedEntityGeneratorGenerator('MyTable2');
        $this->assertEquals('<?php return $this->generate("MyTable2");', $object->generate());
    }
}
