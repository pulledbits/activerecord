<?php
namespace ActiveRecord;

class Prototype
{
	/**
	 * 
	 * @var PHPClass
	 */
	private $class;
	
	public function __construct(PHPClass $class)
	{
		$this->class = $class;
	}
	
	/**
	 * 
	 * @param resource $stream
	 * @return integer
	 */
	public function writeOut($stream)
	{
		return fwrite($stream, $this->class->generate());
	}
}