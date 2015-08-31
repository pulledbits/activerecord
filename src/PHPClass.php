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
	private $memberVariables = array();

	/**
	 * 
	 * @var array
	 */
	private $memberFunctions = array();
	
	/**
	 * 
	 * @var boolean
	 */
	private $final = false;
	
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
	
	private function extractArgumentsFromMethodBody(array $methodBody)
	{
	    $arguments = array();
	    foreach ($methodBody as $methodBodyLine) {
	        if (preg_match_all('/\$\w+/', $methodBodyLine, $matches, PREG_PATTERN_ORDER) > 0) {
	            foreach ($matches[0] as $match) {
	                if ($match === '$this') {
        	            // reference to self
        	        } else {
        	            $arguments[] = $match;
        	        }
	            }
	        }
	    }
	    return array_unique($arguments);
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
		$this->addPublicMethod('__construct', array(
		    "\$this->" . $name . ' = $' . $name . ';'
		));
	}
	
	public function preventInheritance()
	{
	    $this->final = true;
	}
	
	private function getClassLine()
	{
	    $classLine = "";
	    if ($this->final) {
	        $classLine = "final ";
	    }
	    $classLine .= "class " . $this->name;
	    return $classLine;
	}
	
	/**
	 * @return string
	 */
	public function generate()
	{
		$lines = array($this->getClassLine(), "{");
		foreach ($this->memberVariables as $memberVariableIdentifier => $memberVariable) {
			$lines[] = "\t" . $memberVariable['access'] . ' $' . $memberVariableIdentifier . ';';
		}
		foreach ($this->memberFunctions as $memberFunctionIdentifier => $memberFunction) {
			$lines[] = "\t" . $memberFunction['access'] . ' function ' . $memberFunctionIdentifier . '(' . join(', ', $this->extractArgumentsFromMethodBody($memberFunction['body'])) . ')';
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