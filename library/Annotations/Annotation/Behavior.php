<?php

namespace Behavior\Annotations\Annotation;

/**
 * Description of Behavior
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class Behavior extends \Behavior\Annotations\Annotation
{
    const KEYWORD_AND = 'and';
    const KEYWORD_GIVEN = 'given';
    const KEYWORD_WHEN = 'when';
    const KEYWORD_THEN = 'then';
    
    protected function parseLine($line)
    {
        list($keyword, $statement) = explode(' ', $line, 2);
        return $statement;
    }


    protected function parseValue($value)
    {
        $lines = explode(chr(10), $value);
        
        $parsedValue = array(
            'description' => array_shift($lines),
            self::KEYWORD_GIVEN => null,
            self::KEYWORD_WHEN => null,
            self::KEYWORD_THEN => null
        );
        
        $lineBuffer = array();
        foreach ($lines as $lineIdentifier => $line) {
            if (isset($parsedValue[self::KEYWORD_GIVEN])) {
                
            } elseif (stripos($line, self::KEYWORD_GIVEN) === 0) {
                $lineBuffer[] = $this->parseLine($line);
                continue;
                
            } elseif (stripos($line, self::KEYWORD_AND) === 0) {
                $lineBuffer[] = $this->parseLine($line);
                continue;
                
            } elseif (stripos($line, self::KEYWORD_WHEN) === 0) {
                $parsedValue[self::KEYWORD_GIVEN] = $lineBuffer;
                $lineBuffer = array();
                
                $lineBuffer[] = $this->parseLine($line);
                continue;
                
            } else {
                throw new \Behavior\Exception\InvalidArgumentException('Unpected line ' . $line . ' at ' . $lineIdentifier);
            }
            
            
            if (isset($parsedValue[self::KEYWORD_WHEN])) {
                
            } elseif (stripos($line, self::KEYWORD_WHEN) === 0) {
                $lineBuffer[] = $this->parseLine($line);
                continue;
                
            } elseif (stripos($line, self::KEYWORD_AND) === 0) {
                $lineBuffer[] = $this->parseLine($line);
                continue;
                
            } elseif (stripos($line, self::KEYWORD_THEN) === 0) {
                $parsedValue[self::KEYWORD_WHEN] = $lineBuffer;
                
                $parsedValue[self::KEYWORD_THEN] = $this->parseLine($line);
                break;
          
            } else {
                throw new \Behavior\Exception\InvalidArgumentException('Unpected line ' . $line . ' at ' . $lineIdentifier);
                
            }
            
        }
        
        return $parsedValue;
    }
}
