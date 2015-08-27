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
	
	/**
	 * @return string
	 */
	public function generate()
	{
		return '$' . $this->name;
	}
}