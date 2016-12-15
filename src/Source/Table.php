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
    
    private function describeMethod(array $parameters, array $body) : array {
        return [
            'parameters' => $parameters,
            'body' => $body
        ];
    }

    private function describeBodySelect(string $fields, string $from, array $where) : array {
        $whereParameters = [];
        foreach ($where as $referencedColumnName => $parameterIdentifier) {
            $whereParameters[] = '\'' . $referencedColumnName . '\' => $this->' . $parameterIdentifier;
        }
        return ['return $this->schema->select("' . $from . '", [', join(',' . PHP_EOL, $whereParameters), ']);'];
    }
    
    public function describe($namespace) : array {
        if (substr($namespace, -1) != "\\") {
            $namespace .= "\\";
        }

        $columnIdentifiers = array_keys($this->dbalSchemaTable->getColumns());

        $tableIdentifier = $this->dbalSchemaTable->getName();
        
        $methods = [
            'fetchAll' => $this->describeMethod([], $this->describeBodySelect('*', $tableIdentifier, []))
        ];

        $recordClassDefaultUpdateValues = [];
        foreach ($columnIdentifiers as $columnIdentifier) {
            $recordClassDefaultUpdateValues[] = '\'' . $columnIdentifier . '\' => $this->' . $columnIdentifier;
        }

        $methods['__set'] = $this->describeMethod(["property" => 'string', "value" => 'string'], [
            'if (property_exists($this, $property)) {',
            '$this->$property = $value;',
            '$this->schema->update("' . $tableIdentifier . '", [' . join(',' . PHP_EOL, $recordClassDefaultUpdateValues) . '], ["id" => $this->id]);',
            '}'
        ]);

        foreach ($this->dbalSchemaTable->getForeignKeys() as $foreignKeyIdentifier => $foreignKey) {
            $words = explode('_', $foreignKeyIdentifier);
            $camelCased = array_map('ucfirst', $words);
            $foreignKeyMethodIdentifier = join('', $camelCased);

            $fkLocalColumns = $foreignKey->getLocalColumns();

            $where = array_combine($foreignKey->getForeignColumns(), $fkLocalColumns);
            $query = $this->describeBodySelect('*', $foreignKey->getForeignTableName(), $where);
            
            $methods["fetchBy" . $foreignKeyMethodIdentifier] = $this->describeMethod([], $query);
        }
        
        return [
            'identifier' => $namespace . $tableIdentifier,

            'properties' => [
                'columns' => $columnIdentifiers
            ],
            'methods' => $methods
        ];
    }
    
}