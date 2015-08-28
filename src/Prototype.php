<?php
namespace ActiveRecord;

class Prototype
{
	/**
	 * 
	 * @var PHPClass
	 */
	private $class;
	
	public function __construct(PHPClass $class)
	{
		$this->class = $class;
	}
	
	public function addProperty($propertyIdentifier)
	{
		$this->class->addPrivateInstanceVariable($propertyIdentifier);
	}
}