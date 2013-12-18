<?php

namespace Behavior\PHP;

/**
 * Description of Annotations
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class Factory
{
    
    /**
     *
     * @var \Behavior\Factory
     */
    protected $factory;
    
    public function __construct(\Behavior\Factory $factory)
    {
        $this->factory = $factory;
    }

    public function makePHPClass($identifier)
    {
        return new PHPClass($this, $identifier);
    }
}
