<?php
namespace ActiveRecord\Source;

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
            'where' => $where
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

            $fkLocalColumns = $foreignKey->getLocalColumns();
            $where = array_combine($foreignKey->getForeignColumns(), $fkLocalColumns);
            $query = $this->describeQuerySelect('*', $foreignKey->getForeignTableName(), $where);
            
            $methods["fetchBy" . $foreignKeyMethodIdentifier] = $this->describeQueryMethod($fkLocalColumns, $query);
        }
        
        return [
            'identifier' => $namespace . 'Table\\' . $this->dbalSchemaTable->getName(),
            'record-identifier' => $namespace . 'Record\\' . $this->dbalSchemaTable->getName(),

            'properties' => [
                'columns' => array_keys($this->dbalSchemaTable->getColumns())
            ],
            'methods' => $methods
        ];
    }
    
}