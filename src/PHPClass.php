<?php
namespace ActiveRecord;

class PHPClass
{
	/**
	 * 
	 * @var string
	 */
	private $name;
	
	/**
	 * 
	 * @var array
	 */
	private $constructorArguments = array();

	/**
	 * 
	 * @var array
	 */
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
	
	public function dependsOn($name)
	{
		$this->addPrivateInstanceVariable($name);
		$this->constructorArguments[$name] = array();
	}
	
	/**
	 * @return string
	 */
	public function generate()
	{
		$lines = array("class " . $this->name, "{");
		if (count($this->constructorArguments) > 0) {
			$constructorArguments = array();
			foreach ($this->constructorArguments as $constructorArgumentIdentifier => $constructorArgument) {
				$constructorArguments[] = '$' . $constructorArgumentIdentifier;
			}
			$lines[] = "\tpublic function __construct(" . join(", ", $constructorArguments) . ")";
			$lines[] = "\t{";
			$lines[] = "\t\$this->" . $constructorArgumentIdentifier . ' = $' . $constructorArgumentIdentifier . ';';
			$lines[] = "\t}";
		}
		foreach ($this->memberVariables as $memberVariableIdentifier => $memberVariable) {
			$lines[] = "\t" . $memberVariable['access'] . ' $' . $memberVariableIdentifier . ';';
		}
		$lines[] = "}";
		$lines[] = "";
		
		return join(PHP_EOL, $lines);
	}
}