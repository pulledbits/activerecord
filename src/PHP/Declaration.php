<?php
namespace ActiveRecord\PHP;

class Declaration
{
	/**
	 * 
	 * @var string
	 */
	private $typeIdentifier;

	/**
	 *
	 * @var Variable
	 */
	private $variable;
	
	public function __construct($typeIdentifier, Variable $variable)
	{
		$this->typeIdentifier = $typeIdentifier;
		$this->variable = $variable;
	}

	/**
	 * @return string
	 */
	public function generate()
	{
		return $this->typeIdentifier . ' ' . $this->variable->generate();
	}
}