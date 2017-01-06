<?php
namespace ActiveRecord\Source;

final class Table
{

    /**
     * @var string
     */
    private $namespace;

    /**
     * Table constructor.
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    private function describeMethod(bool $static, array $parameters, array $body) : array {
        return [
            'static' => $static,
            'parameters' => $parameters,
            'body' => $body
        ];
    }

    private function makeArrayMapping(string $keyIdentifier, string $variableIdentifier) : string {
        return '\'' . $keyIdentifier . '\' => ' . $variableIdentifier;
    }
    private function makeArrayMappingToProperty(string $keyIdentifier, string $propertyIdentifier) {
        return $this->makeArrayMapping($keyIdentifier, '$this->__get(\'' . $propertyIdentifier . '\')');
    }
    
    public function describe(\Doctrine\DBAL\Schema\Table $dbalSchemaTable) : array {
        if (substr($this->namespace, -1) != "\\") {
            $this->namespace .= "\\";
        }

        $columnIdentifiers = array_keys($dbalSchemaTable->getColumns());

        $tableIdentifier = $dbalSchemaTable->getName();

        $methods = [
            '__construct' => $this->describeMethod(false, ["table" => '\ActiveRecord\Table', "values" => 'array'], [
                '$this->table = $table;',
                '$this->values = $values;'
            ])
        ];

        $primaryKeyWhere = [];
        $properties = [
            'table' => ['\ActiveRecord\Table', ['static' => false, 'value' => null]],
            'values' => ['array', ['static' => false, 'value' => null]]
        ];
        foreach ($columnIdentifiers as $columnIdentifier) {
            if ($dbalSchemaTable->hasPrimaryKey() === false) {
                // no primary key
            } elseif (in_array($columnIdentifier, $dbalSchemaTable->getPrimaryKeyColumns())) {
                $primaryKeyWhere[] = $this->makeArrayMappingToProperty($columnIdentifier, $columnIdentifier);
            }
        }

        $methods['__set'] = $this->describeMethod(false, ["property" => 'string', "value" => 'string'], [
            'if (count($this->table->update([$property => $this->__get($property)], $this->primaryKey())) > 0) {',
            '$this->values[$property] = $value;',
            '}'
        ]);
        $methods['__get'] = $this->describeMethod(false, ["property" => 'string'], [
            'return $this->values[$property];'
        ]);
        $methods['primaryKey'] = $this->describeMethod(false, [], [
            'return [' . join(', ', $primaryKeyWhere) . '];'
        ]);


        $methods['delete'] = $this->describeMethod(false, [], [
            'return $this->table->delete($this->primaryKey());'
        ]);

        foreach ($dbalSchemaTable->getForeignKeys() as $foreignKeyIdentifier => $foreignKey) {
            $words = explode('_', $foreignKeyIdentifier);
            $camelCased = array_map('ucfirst', $words);
            $foreignKeyMethodIdentifier = join('', $camelCased);

            $fkLocalColumns = $foreignKey->getLocalColumns();
            $where = array_combine($foreignKey->getForeignColumns(), $fkLocalColumns);

            $whereParameters = [];
            foreach ($where as $referencedColumnName => $parameterIdentifier) {
                $whereParameters[] = $this->makeArrayMappingToProperty($referencedColumnName, $parameterIdentifier);
            }

            $query = ['return $this->table->selectFrom("' . $foreignKey->getForeignTableName() . '", [\'' . join('\', \'', $foreignKey->getForeignColumns()) . '\'], [', join(',' . PHP_EOL, $whereParameters), ']);'];
            
            $methods["fetchBy" . $foreignKeyMethodIdentifier] = $this->describeMethod(false, [], $query);
        }
        
        return [
            'identifier' => $this->namespace . $tableIdentifier,
            'interfaces' => ['\\ActiveRecord\\WritableRecord'],
            'properties' => $properties,
            'methods' => $methods
        ];
    }
    
}