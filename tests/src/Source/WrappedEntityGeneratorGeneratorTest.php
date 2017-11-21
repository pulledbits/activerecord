<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:30
 */

namespace pulledbits\ActiveRecord\Source;


use pulledbits\ActiveRecord\Source\ConfiguratorGenerator\WrappedEntity;
use function pulledbits\ActiveRecord\Test\createMockStreamInterface;

class WrappedEntityGeneratorGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $stream;

    protected function setUp()
    {
        $this->stream = createMockStreamInterface();
    }

    public function testGenerate_When_DefaultState_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $object = new WrappedEntity('MyTable');

        $object->generate($this->stream);
        $this->stream->seek(0);

        $this->assertEquals('<?php return $this->generate("MyTable");', $this->stream->getContents());
    }
    public function testGenerate_When_OtherTable_Expect_EntityGeneratorWrappingOtherPHPCode() {
        $object = new WrappedEntity('MyTable2');

        $object->generate($this->stream);
        $this->stream->seek(0);

        $this->assertEquals('<?php return $this->generate("MyTable2");', $this->stream->getContents());
    }
}
