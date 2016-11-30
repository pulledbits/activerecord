<?php
namespace ActiveRecord;

final class Table
{
    private $identifier;
    
    public function __construct(String $identifier) {
        $this->identifier = $identifier;
    }
    
    public function describe() : array {
        return [
            'identifier' => $this->identifier
        ];
    }
    
}