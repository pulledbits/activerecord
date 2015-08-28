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

	/**
	 * 
	 * @var array
	 */
	private $memberFunctions = array();
	
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
	 * 
	 * @param string $methodIdentifier
	 * @param array $methodBody
	 */
	public function addPublicMethod($methodIdentifier, array $methodBody)
	{
		$this->memberFunctions[$methodIdentifier] = array(
			'body' => $methodBody,
			'access' => 'public'
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
		foreach ($this->memberFunctions as $memberFunctionIdentifier => $memberFunction) {
			$lines[] = "\t" . $memberFunction['access'] . ' function ' . $memberFunctionIdentifier . '()';
			$lines[] = "\t{";
			foreach ($memberFunction['body'] as $memberFunctionBodyLine) {
				$lines[] = "\t\t" . $memberFunctionBodyLine;
			}
			$lines[] = "\t}";
		}
		$lines[] = "}";
		$lines[] = "";
		
		return join(PHP_EOL, $lines);
	}
}