<?php

namespace Behavior\Annotations\Annotation;

/**
 * Description of Param
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class Param extends \Behavior\Annotations\Annotation
{
    protected function parseValue(array $lines)
    {        
        if (preg_match('/(?P<dataType>[\w\\\\]+(\[\])?)\h+(?P<identifier>\$[a-zA-Z_]\w*)(\h+(?P<description>.*)?|\h*)$/', array_shift($lines), $matches) !== 1) {
            throw new \Behavior\Exception\InvalidArgumentException('Param value does not match expected pattern');
        }
        
        return array(
            'dataType' => $matches['dataType'],
            'identifier' => $matches['identifier'],
            'description' => (isset($matches['description']) ? $matches['description'] : null) . PHP_EOL . join(PHP_EOL, $lines),
        );
        
    }
}
