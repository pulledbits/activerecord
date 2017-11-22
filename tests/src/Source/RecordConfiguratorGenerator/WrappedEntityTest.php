<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:30
 */

namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;


use function pulledbits\ActiveRecord\Test\createMockStreamInterface;

class WrappedEntityTest extends \PHPUnit_Framework_TestCase
{
    private $stream;

    protected function setUp()
    {
        $this->stream = createMockStreamInterface();
    }

    public function testGenerate_When_DefaultState_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $object = new WrappedEntity('MyTable');

        $object->generateConfigurator($this->stream);
        $this->stream->seek(0);

        $this->assertEquals(PHP_EOL . 'return $this->generate("MyTable");', $this->stream->getContents());
    }
    public function testGenerate_When_OtherTable_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $object = new WrappedEntity('MyTable2');

        $object->generateConfigurator($this->stream);
        $this->stream->seek(0);

        $this->assertEquals(PHP_EOL . 'return $this->generate("MyTable2");', $this->stream->getContents());
    }
}
