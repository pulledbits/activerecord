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

    private function describeBodySelect(array $fields, string $from, array $where) : array {
        $aliassedFields = [];
        foreach ($fields as $fieldIdentifier) {
            $aliassedFields[] = $this->makeArrayMapping('_' . $fieldIdentifier, '\'' . $fieldIdentifier . '\'');
        }

        $whereParameters = [];
        foreach ($where as $referencedColumnName => $parameterIdentifier) {
            $whereParameters[] = $this->makeArrayMappingToProperty($referencedColumnName, $this->makePropertyIdentifierFromColumnIdentifier($parameterIdentifier));
        }

        return ['return $this->schema->select("' . $from . '", [' . join(', ', $aliassedFields) . '], [', join(',' . PHP_EOL, $whereParameters), ']);'];
    }

    private function makePropertyIdentifierFromColumnIdentifier(string $columnIdentifier) : string {
        return '_' . $columnIdentifier;
    }

    private function makeArrayMapping(string $keyIdentifier, string $variableIdentifier) : string {
        return '\'' . $keyIdentifier . '\' => ' . $variableIdentifier;
    }
    private function makeArrayMappingToProperty(string $keyIdentifier, string $propertyIdentifier) {
        return $this->makeArrayMapping($keyIdentifier, '$this->' . $propertyIdentifier);
    }
    
    public function describe($namespace) : array {
        if (substr($namespace, -1) != "\\") {
            $namespace .= "\\";
        }

        $columnIdentifiers = array_keys($this->dbalSchemaTable->getColumns());

        $tableIdentifier = $this->dbalSchemaTable->getName();

        $methods = [
            '__construct' => $this->describeMethod(["schema" => '\ActiveRecord\Schema'], ['$this->schema = $schema;']),
            'fetchAll' => $this->describeMethod([], $this->describeBodySelect($columnIdentifiers, $tableIdentifier, []))
        ];

        $primaryKeyDefaultValue = $primaryKeyWhere = $defaultUpdateValues = [];
        $properties = [
            'primaryKey' => 'array',
            'schema' => '\ActiveRecord\Schema'
        ];
        foreach ($columnIdentifiers as $columnIdentifier) {
            $properties[$this->makePropertyIdentifierFromColumnIdentifier($columnIdentifier)] = 'string';
            $defaultUpdateValues[] = $this->makeArrayMappingToProperty($columnIdentifier, $this->makePropertyIdentifierFromColumnIdentifier($columnIdentifier));

            if ($this->dbalSchemaTable->hasPrimaryKey() === false) {
                // no primary key
            } elseif (in_array($columnIdentifier, $this->dbalSchemaTable->getPrimaryKeyColumns())) {
                $primaryKeyDefaultValue[] = '_' . $columnIdentifier;
                $primaryKeyWhere[] = $this->makeArrayMappingToProperty($columnIdentifier, $this->makePropertyIdentifierFromColumnIdentifier($columnIdentifier));
            }
        }

        if (count($primaryKeyDefaultValue) === 0) {
            $methods['__construct']['body'][] = '$this->primaryKey = [];';
        } else {
            $methods['__construct']['body'][] = '$this->primaryKey = [\'' . join(',', $primaryKeyDefaultValue) . '\'];';
        }

        $methods['__set'] = $this->describeMethod(["property" => 'string', "value" => 'string'], [
            'if (property_exists($this, $property)) {',
            '$this->$property = $value;',
            '$this->schema->update("' . $tableIdentifier . '", [' . join(',' . PHP_EOL, $defaultUpdateValues) . '], [' . join(',' . PHP_EOL, $primaryKeyWhere) . ']);',
            '}'
        ]);

        foreach ($this->dbalSchemaTable->getForeignKeys() as $foreignKeyIdentifier => $foreignKey) {
            $words = explode('_', $foreignKeyIdentifier);
            $camelCased = array_map('ucfirst', $words);
            $foreignKeyMethodIdentifier = join('', $camelCased);

            $fkLocalColumns = $foreignKey->getLocalColumns();
            $where = array_combine($foreignKey->getForeignColumns(), $fkLocalColumns);
            $query = $this->describeBodySelect($foreignKey->getForeignColumns(), $foreignKey->getForeignTableName(), $where);
            
            $methods["fetchBy" . $foreignKeyMethodIdentifier] = $this->describeMethod([], $query);
        }
        
        return [
            'identifier' => $namespace . $tableIdentifier,

            'properties' => $properties,
            'methods' => $methods
        ];
    }
    
}