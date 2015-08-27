<?php
namespace ActiveRecord;

class PHPClassTest extends \PHPUnit_Framework_TestCase
{
	public function testGeneratePHPClassGivesEmptyPHPClass()
	{
		$class = new PHPClass("ActiveRecord");
		$this->assertEquals("class ActiveRecord\n{\n}\n", $class->generate());
	}
	

	public function testPHPClassWithPrivateInstanceVariable()
	{
		$class = new PHPClass("PersonRecord");
		$class->addPrivateInstanceVariable("name");
		$this->assertEquals("class PersonRecord\n{\n\tprivate \$name;\n}\n", $class->generate());
	}
}