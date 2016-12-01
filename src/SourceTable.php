<?php
namespace ActiveRecord;

final class SourceTable
{
    /**
     * 
     * @var \Doctrine\DBAL\Schema\Table
     */
    private $dbalSchemaTable;
    
    public function __construct(\Doctrine\DBAL\Schema\Table $dbalSchemaTable) {
        $this->dbalSchemaTable = $dbalSchemaTable;
    }
    
    private function describeQueryMethod(array $parameters, array $query) : array {
        return [
            'parameters' => $parameters,
            'query' => $query
        ];
    }
    
    private function describeQuery(string $queryType, array $query) : array {
        return [$queryType, $query];
    }
    private function describeQuerySelect(string $fields, string $from, array $where) : array {
        return $this->describeQuery('SELECT', [
            'fields' => $fields,
            'from' => $from,
            'where' => join(' AND ', $where)
        ]);
    }
    
    public function describe($namespace) : array {
        if (substr($namespace, -1) != "\\") {
            $namespace .= "\\";
        }
        
        $tableIdentifier = $this->dbalSchemaTable->getName();
        
        $methods = [
            'fetchAll' => $this->describeQueryMethod([], $this->describeQuerySelect('*', $tableIdentifier, []))
        ];
        foreach ($this->dbalSchemaTable->getForeignKeys() as $foreignKeyIdentifier => $foreignKey) {
            $words = explode('_', $foreignKeyIdentifier);
            $camelCased = array_map('ucfirst', $words);
            $foreignKeyMethodIdentifier = join('', $camelCased);
            
            $where = array_map(function($methodParameter) {
                return $methodParameter . ' = :' . $methodParameter;
            }, $foreignKey->getLocalColumns());
            $query = $this->describeQuerySelect('*', $tableIdentifier, $where);
            
            $methods["fetchBy" . $foreignKeyMethodIdentifier] = $this->describeQueryMethod($foreignKey->getLocalColumns(), $query);
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