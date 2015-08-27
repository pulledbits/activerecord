<?php
namespace ActiveRecord;

class PHPClass
{
	private $name;
	
	public function __construct($name)
	{
		$this->name = $name;
	}
	
	public function generate()
	{
		return "class {$this->name}\n{\n}\n";
	}
}