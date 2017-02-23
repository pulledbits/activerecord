<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:30
 */

namespace pulledbits\ActiveRecord\Source;


class WrappedEntityGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate_When_DefaultState_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $object = new WrappedEntityGenerator('MyTable');
        $this->assertEquals('<?php return require __DIR__ . DIRECTORY_SEPARATOR . "MyTable.php";', $object->generate());
    }
}
