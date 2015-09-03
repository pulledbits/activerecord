<?php
namespace ActiveRecord;

class PrototypeTest extends \PHPUnit_Framework_TestCase
{
	public function testPrototypeAcceptsAProperty()
	{
		$class = new PHP\Class_('PersonRecord');
		$prototype = new Prototype($class);
		$prototype->addProperty('name');
		$this->assertEquals("final class PersonRecord" . PHP_EOL .
				"{" . PHP_EOL .
				"\tprivate \$repository;" . PHP_EOL .
				"\tprivate \$name;" . PHP_EOL .
		        "\tpublic function __construct(\$repository)" . PHP_EOL .
				"\t{" . PHP_EOL .
				"\t\t\$this->repository = \$repository;" . PHP_EOL .
				"\t}" . PHP_EOL . 
				"\tpublic function getName()" . PHP_EOL . 
				"\t{" . PHP_EOL .
				"\t\treturn \$this->name;" . PHP_EOL .
				"\t}" . PHP_EOL . 
				"\tpublic function setName(\$name)" . PHP_EOL . 
				"\t{" . PHP_EOL .
				"\t\t\$this->name = \$name;" . PHP_EOL .
				"\t}" . PHP_EOL . 
				"}" . PHP_EOL . "", $class->generate());
	}
	
}