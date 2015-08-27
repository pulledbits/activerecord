<?php
namespace ActiveRecord;

class PHPClassTest extends \PHPUnit_Framework_TestCase
{
	public function testGeneratePHPClassGivesEmptyPHPClass()
	{
		$class = new PHPClass("ActiveRecord");
		$this->assertEquals("class ActiveRecord" . PHP_EOL . "{" . PHP_EOL . "}" . PHP_EOL . "", $class->generate());
	}
	

	public function testPHPClassWithPrivateInstanceVariable()
	{
		$class = new PHPClass("PersonRecord");
		$class->addPrivateInstanceVariable("name");
		$this->assertEquals("class PersonRecord" . PHP_EOL . "{" . PHP_EOL . "\tprivate \$name;" . PHP_EOL . "}" . PHP_EOL . "", $class->generate());
	}
}