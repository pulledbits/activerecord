<?php
namespace ActiveRecord;

class PHPClassTest extends \PHPUnit_Framework_TestCase
{
	public function testGeneratePHPClassGivesEmptyPHPClass()
	{
		$class = new PHPClass("ActiveRecord");
		$this->assertEquals("class ActiveRecord\n{\n}\n", $class->generate());
	}
}