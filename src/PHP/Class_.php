<?php
namespace ActiveRecord\PHP;

class Class_
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
	        if (preg_match_all('/\$(\w+)/', $methodBodyLine, $matches, PREG_SET_ORDER) > 0) {
	            foreach ($matches as $match) {
	                if ($match[0] === '$this') {
        	            // reference to self
        	        } else {
        	            $arguments[$match[1]] = $match[0];
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
			'access' => 'public',
		    'parameters' => array()
		);
	}
	
	public function dependsOn(Declaration $variableDeclaration)
	{
	    $dependencyIdentifier = $variableDeclaration->getVariable()->getName();
		$this->addPrivateInstanceVariable($dependencyIdentifier);
		$this->addPublicMethod('__construct', array(
		    "\$this->" . $dependencyIdentifier . ' = $' . $dependencyIdentifier . ';'
		));
		$this->memberFunctions['__construct']['parameters'][$dependencyIdentifier] = $variableDeclaration;
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
		    $parameters = array();
		    foreach ($memberFunction['parameters'] as $parameterIdentifier => $parameterDeclaration) {
		        $parameters[$parameterIdentifier] = $parameterDeclaration->generate();
		    }
		    $parameters = array_merge($this->extractArgumentsFromMethodBody($memberFunction['body']), $parameters);
			$lines[] = "\t" . $memberFunction['access'] . ' function ' . $memberFunctionIdentifier . '(' . join(', ', $parameters) . ')';
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