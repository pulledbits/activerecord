<?php

namespace Behavior\PHP;

/**
 * Description of PHPClass
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class PHPClass extends \Behavior\PHP
{
    protected $identifier;
    
    public function __construct(\Behavior\PHP\Factory $factory, $identifier)
    {
        parent::__construct($factory);
        
        $this->identifier = $identifier;
    }
    
    protected function generate()
    {
        return array(
            'class ' . $this->identifier,
            '{',
            '}'
        );
    }

}
