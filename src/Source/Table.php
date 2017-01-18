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
        if (substr($this->namespace, -1) != "\\") {
            $this->namespace .= "\\";
        }
    }

    private function describeMethod(bool $static, array $parameters, array $body) : array {
        return [
            'static' => $static,
            'parameters' => $parameters,
            'body' => $body
        ];
    }

    private function describeConstructorMethod() : array {
        return $this->describeMethod(false, ["table" => '\ActiveRecord\Table', "values" => 'array'], [
            '$this->table = $table;',
            '$this->values = $values;'
        ]);
    }

    private function describeSetterMethod() {
        return $this->describeMethod(false, ["property" => 'string', "value" => 'string'], [
            'if (count($this->table->update([$property => $this->values[$property]], $this->primaryKey())) > 0) {',
            '$this->values[$property] = $value;',
            '}'
        ]);
    }

    private function describeGetterMethod() {
        return $this->describeMethod(false, ["property" => 'string'], [
            'return $this->values[$property];'
        ]);
    }

    private function describePrimaryKeyMethod(\Doctrine\DBAL\Schema\Table $dbalSchemaTable) {
        $primaryKeyWhere = [];
        if ($dbalSchemaTable->hasPrimaryKey()) {
            $primaryKeyWhere = $this->makeArrayMappingToProperty($dbalSchemaTable->getPrimaryKeyColumns(), $dbalSchemaTable->getPrimaryKeyColumns());
        }
        return $this->describeMethod(false, [], [
            'return [' . join(', ', $primaryKeyWhere) . '];'
        ]);
    }

    private function describeDeleteMethod() {
        return $this->describeMethod(false, [], [
            'return $this->table->delete($this->primaryKey());'
        ]);
    }

    private function describeFetchByFKMethod(\Doctrine\DBAL\Schema\ForeignKeyConstraint $foreignKey) {
        $fkColumns = $foreignKey->getForeignColumns();
        return $this->describeMethod(false, [], [
            'return $this->table->selectFrom("' . $foreignKey->getForeignTableName() . '", [\'' . join('\', \'', $fkColumns) . '\'], [', join(',' . PHP_EOL, $this->makeArrayMappingToProperty($fkColumns, $foreignKey->getLocalColumns())), ']);'
        ]);
    }

    private function makeArrayMappingToProperty(array $keyColumns, array $propertyColumns) : array {
        return array_map(function($keyIdentifier, $propertyIdentifier) {
            return '\'' . $keyIdentifier . '\' => ' . '$this->values[\'' . $propertyIdentifier . '\']';
        }, $keyColumns, $propertyColumns);
    }

    
    public function describe(\Doctrine\DBAL\Schema\Table $dbalSchemaTable) : array {
        $methods = [
            '__construct' => $this->describeConstructorMethod(),
            '__set' => $this->describeSetterMethod(),
            '__get' => $this->describeGetterMethod(),
            'primaryKey' => $this->describePrimaryKeyMethod($dbalSchemaTable),
            'delete' => $this->describeDeleteMethod()
        ];


        foreach ($dbalSchemaTable->getForeignKeys() as $foreignKeyIdentifier => $foreignKey) {
            $methods["fetchBy" . join('', array_map('ucfirst', explode('_', $foreignKeyIdentifier)))] = $this->describeFetchByFKMethod($foreignKey);
        }
        
        return [
            'identifier' => $this->namespace . $dbalSchemaTable->getName(),
            'interfaces' => ['\\ActiveRecord\\WritableRecord'],
            'traits' => ['\\ActiveRecord\\Record\\WritableTrait'],
            'properties' => [
                'table' => ['\ActiveRecord\Table', ['static' => false, 'value' => null]],
                'values' => ['array', ['static' => false, 'value' => null]]
            ],
            'methods' => $methods
        ];
    }
    
}