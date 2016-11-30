<?php
namespace ActiveRecord;

final class Table
{
    /**
     * 
     * @var \Doctrine\DBAL\Schema\Table
     */
    private $dbalSchemaTable;
    
    public function __construct(\Doctrine\DBAL\Schema\Table $dbalSchemaTable) {
        $this->dbalSchemaTable = $dbalSchemaTable;
    }
    
    public function describe($namespace) : array {
        if (substr($namespace, -1) != "\\") {
            $namespace .= "\\";
        }
        
        return [
            'identifier' => $namespace . $this->dbalSchemaTable->getName(),
            'properties' => array_keys($this->dbalSchemaTable->getColumns())
        ];
    }
    
}