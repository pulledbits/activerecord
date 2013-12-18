<?php

namespace Behavior\Annotated;

/**
 * Description of PHPClass
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class PHPClass extends \Behavior\Annotated
{
    /**
     *
     * @var Subroutine\Method[]
     */
    protected $methods = array();
    
    public function __construct(Factory $factory, \Reflector $reflector)
    {
        parent::__construct($factory, $reflector);
        
        foreach ($reflector->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $this->methods[] = $factory->makeAnnotatedMethod($method);
        }
    }
    
    
    protected function makeAnnotations(\Behavior\Annotations\Factory $annotationsFactory)
    {
        return $annotationsFactory->makeAnnotationsForReflectionClass($this->reflector);
    }

}
