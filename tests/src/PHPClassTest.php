<?php
namespace ActiveRecord;

class PHPClassTest extends \PHPUnit_Framework_TestCase
{
	public function testCreateNewPHPClass()
	{
		$class = new PHPClass("ActiveRecord");
		$this->assertEquals("class ActiveRecord\n{\n}\n", $class->generate());
	}
}