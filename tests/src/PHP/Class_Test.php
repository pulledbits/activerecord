<?php
namespace ActiveRecord\PHP;

class ClassTest extends \PHPUnit_Framework_TestCase
{
	public function testGenerateClassGivesEmptyClass()
	{
		$class = new Class_("ActiveRecord");
		$this->assertEquals("class ActiveRecord" . PHP_EOL . 
				"{" . PHP_EOL . 
				"}" . PHP_EOL . "", $class->generate());
	}

	public function testGenerateFinalClass()
	{
	    $class = new Class_("ActiveRecord");
	    $class->preventInheritance();
	    $this->assertEquals("final class ActiveRecord" . PHP_EOL .
	        "{" . PHP_EOL .
	        "}" . PHP_EOL . "", $class->generate());
	}

	public function testClassWithPrivateInstanceVariable()
	{
		$class = new Class_("PersonRecord");
		$class->addPrivateInstanceVariable("name");
		$this->assertEquals("class PersonRecord" . PHP_EOL . 
				"{" . PHP_EOL . 
				"\tprivate \$name;" . PHP_EOL . 
				"}" . PHP_EOL . "", $class->generate());
	}
	

	public function testClassWithDependency()
	{
		$class = new Class_("PersonRecord");
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
	
	public function testClassWithGetter()
	{
		$class = new Class_("PersonRecord");
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