<?php

namespace Behavior;

/**
 * Description of PHP
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
abstract class PHP
{
    /**
     *
     * @var PHP\Factory
     */
    protected $factory;
    
    public function __construct(PHP\Factory $factory)
    {
        $this->factory = $factory;
    }
    
    abstract protected function generate();
    
    final public function __toString()
    {
        return join(PHP_EOL, $this->generate());
    }
}
