<?php
namespace ActiveRecord\PHP;

class VariableTest extends \PHPUnit_Framework_TestCase
{
	public function testVariableGenerated()
	{
		$variable = new Variable('repository');
		$this->assertEquals('$repository', $variable->generate());
	}
	
	public function testVariableDeclaration()
	{
		$variable = new Variable('repository');
		$this->assertEquals('Repository $repository', $variable->declareAs('Repository'));
	}
}