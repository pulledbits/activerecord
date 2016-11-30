<?php
namespace ActiveRecord;

final class Table
{
    private $identifier;
    
    public function __construct(String $identifier) {
        $this->identifier = $identifier;
    }
    
    public function describe($namespace) : array {
        if (substr($namespace, -1) != "\\") {
            $namespace .= "\\";
        }

        return [
            'identifier' => $namespace . $this->identifier
        ];
    }
    
}