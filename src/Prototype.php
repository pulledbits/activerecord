<?php
namespace ActiveRecord;

class Prototype
{
	/**
	 * 
	 * @var PHPClass
	 */
	private $class;
	
	public function __construct(PHP\Class_ $class, PHP\Declaration $repositoryDeclaration)
	{
		$this->class = $class;
		$this->class->preventInheritance();
		$this->class->dependsOn($repositoryDeclaration);
	}
	
	/**
	 * 
	 * @param string $propertyIdentifier
	 */
	public function addProperty($propertyIdentifier)
	{
		$this->class->addPrivateInstanceVariable($propertyIdentifier);
		$this->class->addPublicMethod('get' . ucfirst($propertyIdentifier), array(
			"return \$this->{$propertyIdentifier};"
		));
		$this->class->addPublicMethod('set' . ucfirst($propertyIdentifier), array(
			"\$this->{$propertyIdentifier} = \$name;"
		));
	}
}