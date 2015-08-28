<?php
namespace ActiveRecord;

class PrototypeTest extends \PHPUnit_Framework_TestCase
{
	public function testPrototypeAcceptsAProperty()
	{
		$class = new PHPClass('PersonRecord');
		$prototype = new Prototype($class);
		$prototype->addProperty('name');
		$this->assertEquals("class PersonRecord" . PHP_EOL .
				"{" . PHP_EOL .
				"\tprivate \$name;" . PHP_EOL .
				"}" . PHP_EOL . "", $class->generate());
	}
	
	public function testPrototypeWritesToStream()
	{
		$class = new PHPClass('PersonRecord');
		$prototype = new Prototype($class);
		$prototype->addProperty('name');
		
		$stream = fopen('php://memory', 'wb');
		$prototype->writeOut($stream);
		fseek($stream, 0);
		
		$this->assertEquals("class PersonRecord" . PHP_EOL .
				"{" . PHP_EOL .
				"\tprivate \$name;" . PHP_EOL .
				"}" . PHP_EOL . "", fread($stream, 512));
	}
	
}