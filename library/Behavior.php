<?php

/**
 * Description of Behavior
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class Behavior
{ 
    
    /**
     *
     * @var \Behavior\Factory
     */
    protected $factory;


    public function __construct(Behavior\Factory $factory)
    {
        $this->factory = $factory;
    }
    
    public function execute($baseDirectory, $baseNamespace, $className)
    {
        if (Behavior\autoload($baseNamespace, $baseDirectory) === false) {
            throw new Behavior\Exception\FailedAutoload('Failed autoloading ' . $baseDirectory);
        }
        
        $annotatedFactory = $this->factory->makeAnnotatedFactory();
        
        $annotatedClass = $annotatedFactory->makeAnnotatedClass(new \ReflectionClass($className));
        
        var_dump($annotatedClass);
    }
}
