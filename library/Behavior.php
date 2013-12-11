<?php

/**
 * Description of Behavior
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class Behavior
{   
    public function execute($baseDirectory, $baseNamespace, $className)
    {
        if (Behavior\autoload($baseNamespace, $baseDirectory) === false) {
            throw new Behavior\Exception\FailedAutoload('Failed autoloading ' . $baseDirectory);
        }
        
        $reflection = new ReflectionClass($className);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            var_dump(Behavior\Annotation::parseDocComment($method->getDocComment()));
        }
    }
}
