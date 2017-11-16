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
    protected function setUp()
    {
    }

    public function testGenerate_When_DefaultState_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $object = new WrappedEntityGeneratorGenerator('MyTable');
        $this->assertEquals('<?php return $this->generate("MyTable");', $object->generate());
    }
    public function testGenerate_When_OtherTable_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $object = new WrappedEntityGeneratorGenerator('MyTable2');
        $this->assertEquals('<?php return $this->generate("MyTable2");', $object->generate());
    }
}
