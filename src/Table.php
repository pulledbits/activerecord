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
        
        $methods = [];
        foreach ($this->dbalSchemaTable->getForeignKeys() as $foreignKeyIdentifier => $foreignKey) {
            $words = explode('_', $foreignKeyIdentifier);
            $camelCased = array_map('ucfirst', $words);
            $foreignKeyMethodIdentifier = join('', $camelCased);
            $methods["fetchBy" . $foreignKeyMethodIdentifier] = [
                'parameters' => $foreignKey->getLocalColumns(),
                'query' => ['SELECT', [
                    'fields' => '*',
                    'from' => $this->dbalSchemaTable->getName(),
                    'where' => join(' AND ', array_map(function($methodParameter) {
                        return $methodParameter . ' = :' . $methodParameter;
                    }, $foreignKey->getLocalColumns()))
                ]]
            ];
        }
        
        return [
            'identifier' => $namespace . $this->dbalSchemaTable->getName(),
            'properties' => [
                'columns' => array_keys($this->dbalSchemaTable->getColumns())
            ],
            'methods' => $methods
        ];
    }
    
}