<?php
namespace ActiveRecord;

class PHPVariableTest extends \PHPUnit_Framework_TestCase
{
	public function testVariableGenerated()
	{
		$variable = new PHPVariable('repository');
		$this->assertEquals('$repository', $variable->generate());
	}
	
	public function testVariableDeclaration()
	{
		$variable = new PHPVariable('repository');
		$this->assertEquals('Repository $repository', $variable->declareAs('Repository'));
	}
}