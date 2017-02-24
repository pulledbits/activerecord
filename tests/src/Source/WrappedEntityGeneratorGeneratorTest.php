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
    public function testGenerate_When_DefaultState_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $factory = new GeneratorGeneratorFactory();
        $object = $factory->makeWrappedEntityGeneratorGenerator('MyTable');
        $this->assertEquals('<?php return require __DIR__ . DIRECTORY_SEPARATOR . "MyTable.php";', $object->generate());
    }
    public function testGenerate_When_OtherTable_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $factory = new GeneratorGeneratorFactory();
        $object = $factory->makeWrappedEntityGeneratorGenerator('MyTable2');
        $this->assertEquals('<?php return require __DIR__ . DIRECTORY_SEPARATOR . "MyTable2.php";', $object->generate());
    }
}
