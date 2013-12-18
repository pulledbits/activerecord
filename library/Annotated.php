<?php

namespace Behavior;

/**
 * Description of Annotated
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
abstract class Annotated
{
    /**
     *
     * @var Annotated\Factory
     */
    protected $factory;
    
    /**
     *
     * @var \Reflector
     */
    protected $reflector;
    
    /**
     *
     * @var Annotations 
     */
    protected $annotations;
    
    public function __construct(Annotated\Factory $factory, \Reflector $reflector)
    {
        $this->factory = $factory;
        $this->reflector = $reflector;
        $this->annotations = $this->makeAnnotations($this->factory->makeAnnotationsFactory());
    }
    
    /**
     * @param Annotations\Factory $annotationsFactory
     * @return Annotations;
     */
    abstract protected function makeAnnotations(Annotations\Factory $annotationsFactory);
}
