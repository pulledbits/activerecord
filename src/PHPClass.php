<?php
namespace ActiveRecord;

class PHPClass
{
	private $name;
	private $memberVariables = array();
	
	public function __construct($name)
	{
		$this->name = $name;
	}
	
	/**
	 * 
	 * @param string $name
	 */
	public function addPrivateInstanceVariable($name)
	{
		$this->memberVariables[$name] = array(
			'access' => 'private'
		);
	}
	
	/**
	 * @return string
	 */
	public function generate()
	{
		$lines = array("class {$this->name}", "{");
		foreach ($this->memberVariables as $memberVariableIdentifier => $memberVariable) {
			$lines[] = "\t" . $memberVariable['access'] . ' $' . $memberVariableIdentifier . ';';
		}
		$lines[] = "}";
		$lines[] = "";
		
		return join(PHP_EOL, $lines);
	}
}