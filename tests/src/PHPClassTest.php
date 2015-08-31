<?php
namespace ActiveRecord;

class PHPClassTest extends \PHPUnit_Framework_TestCase
{
	public function testGeneratePHPClassGivesEmptyPHPClass()
	{
		$class = new PHPClass("ActiveRecord");
		$this->assertEquals("class ActiveRecord" . PHP_EOL . 
				"{" . PHP_EOL . 
				"}" . PHP_EOL . "", $class->generate());
	}

	public function testGenerateFinalPHPClass()
	{
	    $class = new PHPClass("ActiveRecord");
	    $class->preventInheritance();
	    $this->assertEquals("final class ActiveRecord" . PHP_EOL .
	        "{" . PHP_EOL .
	        "}" . PHP_EOL . "", $class->generate());
	}

	public function testPHPClassWithPrivateInstanceVariable()
	{
		$class = new PHPClass("PersonRecord");
		$class->addPrivateInstanceVariable("name");
		$this->assertEquals("class PersonRecord" . PHP_EOL . 
				"{" . PHP_EOL . 
				"\tprivate \$name;" . PHP_EOL . 
				"}" . PHP_EOL . "", $class->generate());
	}
	

	public function testPHPClassWithDependency()
	{
		$class = new PHPClass("PersonRecord");
		$class->dependsOn("repository");
		$this->assertEquals("class PersonRecord" . PHP_EOL . 
				"{" . PHP_EOL . 
				"\tprivate \$repository;" . PHP_EOL . 
				"\tpublic function __construct(\$repository)" . PHP_EOL . 
				"\t{" . PHP_EOL .
				"\t\t\$this->repository = \$repository;" . PHP_EOL .
				"\t}" . PHP_EOL .
				"}" . PHP_EOL . "", $class->generate());
	}
	
	public function testPHPClassWithGetter()
	{
		$class = new PHPClass("PersonRecord");
		$class->addPrivateInstanceVariable("name");
		$class->addPublicMethod("getName", array(
			"return \$this->name;"	
		));
		$this->assertEquals("class PersonRecord" . PHP_EOL . 
				"{" . PHP_EOL . 
				"\tprivate \$name;" . PHP_EOL .
				"\tpublic function getName()" . PHP_EOL . 
				"\t{" . PHP_EOL .
				"\t\treturn \$this->name;" . PHP_EOL .
				"\t}" . PHP_EOL . 
				"}" . PHP_EOL . "", $class->generate());
	}
}