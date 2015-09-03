<?php
namespace ActiveRecord\PHP;

class DeclarationTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateDeclaration()
    {
        $variable = new Variable('person');
        $declaration = new Declaration('PersonRecord', $variable);
		$this->assertEquals('PersonRecord $person', $declaration->generate());
    }
    
}