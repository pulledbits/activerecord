<?php
namespace ActiveRecord;

class PHPVariable
{
	/**
	 * 
	 * @var string
	 */
	private $name;
	
	public function __construct($name)
	{
		$this->name = $name;
	}
	
	public function declareAs($typeIdentifier)
	{
		return $typeIdentifier . ' ' . $this->generate();
	}
	
	/**
	 * @return string
	 */
	public function generate()
	{
		return '$' . $this->name;
	}
}