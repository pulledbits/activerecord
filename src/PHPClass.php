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
		$code = "class {$this->name}\n{";
		foreach ($this->memberVariables as $memberVariableIdentifier => $memberVariable) {
			$code .= "\n\t" . $memberVariable['access'] . ' $' . $memberVariableIdentifier;
		}
		$code .= "\n}\n";
		
		return $code;
	}
}