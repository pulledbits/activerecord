<?php

namespace Behavior\Annotated;

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
    
    /**
     * 
     * @return \Behavior\Annotations\Factory
     */
    public function makeAnnotationsFactory()
    {
        return $this->factory->makeAnnotationsFactory();
    }

    /**
     * 
     * @param \ReflectionMethod $method
     * @return Subroutine\Method
     */
    public function makeAnnotatedMethod(\ReflectionMethod $method)
    {
        return new Subroutine\Method($this, $method);
    }

    /**
     * 
     * @param \ReflectionClass $class
     * @return PHPClass
     */
    public function makeAnnotatedClass(\ReflectionClass $class)
    {
        return new PHPClass($this, $class);
    }
}
