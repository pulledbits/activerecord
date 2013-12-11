<?php

namespace Behavior\Annotations;

/**
 * Description of Annotations
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
abstract class Annotation
{

    /**
     *
     * @var \Behavior\Annotations\Factory
     */
    protected $factory;
    
    protected $value;
    
    /**
     * 
     * @param string $value
     */
    final public function __construct(Factory $factory, $value)
    {
        $this->factory = $factory;
        $this->value = $this->parseValue(explode(chr(10), $value));
    }
    
    /**
     * @param array $lines
     */
    abstract protected function parseValue(array $lines);
}
