<?php

namespace Behavior\Annotations\Annotation;

/**
 * Description of Author
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class Author extends \Behavior\Annotations\Annotation
{
    protected function parseValue(array $lines)
    {        
        if (preg_match('/(?<name>[^\<]+)(\<(?<email>[^\>]+)\>)?$/', array_shift($lines), $matches) !== 1) {
            throw new \Behavior\Exception\InvalidArgumentException('Param value does not match expected pattern');
        }
        
        return array(
            'name' => $matches['name'],
            'email' => (isset($matches['email']) ? $matches['email'] : null),
        );
        
    }
}
