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
    
    /**
     * 
     * @param string $path
     * @throws Behavior\Exception\InvalidArgumentException
     */
    public function execute($path, $namespace)
    {
        if (Behavior\autoload($path, $namespace) === false) {
            throw new Behavior\Exception\FailedAutoload('Failed autoloading ' . $path);
        }
        
        print_r($this->iterateClasses($path, $namespace, array()));
    }
        
    protected function iterateClasses($baseDirectory, $baseNamespace, array $path)
    {
        $directory = opendir($baseDirectory . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $path));
        if ($directory === false) {
            throw new \Behavior\Exception\FailedOpenDirectory('could not open directory for reading');
        }
        
        $nodes = array();
        while (($node = readdir($directory)) !== false) {
            if (substr($node, 0, 1) === '.') {
                continue;
            }
            
            $nodes[$node] = $this->processNode($baseDirectory, $baseNamespace, $path, $node);
        }
        closedir($directory);
        return $nodes;
    }
    
    protected function processNode($baseDirectory, $baseNamespace, array $path, $node)
    {
        if (is_dir($baseDirectory . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR . $node)) {
            $path[] = $node;
            return $this->iterateClasses($baseDirectory, $baseNamespace, $path);

        } elseif (substr($node, -4) === '.php') {
            $path[] = basename($node, '.php');
            $annotatedFactory = $this->factory->makeAnnotatedFactory();

            $annotatedClass = $annotatedFactory->makeAnnotatedClass(new \ReflectionClass($baseNamespace . NAMESPACE_SEPARATOR . join(NAMESPACE_SEPARATOR, $path)));

            $testClass = $annotatedClass->makeTest($this->factory->makePHPFactory());
            return $testClass;
        }
    }
}
