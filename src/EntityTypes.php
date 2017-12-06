<?php


namespace pulledbits\ActiveRecord;


use pulledbits\ActiveRecord\SQL\Meta\TableDescription;

class EntityTypes implements \Iterator, \ArrayAccess
{
    private $schema;
    private $entityIdentifiers;
    private $entityTypes;

    public function __construct(Schema $schema, Result $result)
    {
        $this->schema = $schema;
        $this->entityTypes = [];

        foreach ($result->fetchAll() as $baseTable) {
            $tableIdentifier = array_shift($baseTable);
            switch ($baseTable['Table_type']) {
                case 'BASE_TABLE':
                    $this->entityIdentifiers[$tableIdentifier] = 'BASE_TABLE';
                    break;
                case 'VIEW':
                    $this->entityIdentifiers[$tableIdentifier] = 'VIEW';
                    break;
            }
        }
    }

    private function retrieveTableDescription(string $tableIdentifier) : TableDescription
    {
        if (array_key_exists($tableIdentifier, $this->entityIdentifiers) === false) {
            return new TableDescription();
        } elseif (array_key_exists($tableIdentifier, $this->entityTypes) === false) {
            $this->entityTypes[$tableIdentifier] = new TableDescription();
        }

        if ($this->entityIdentifiers[$tableIdentifier] === 'VIEW') {
            $underscorePosition = strpos($tableIdentifier, '_');
            if ($underscorePosition > 0) {
                $possibleEntityTypeIdentifier = substr($tableIdentifier, 0, $underscorePosition);
                $this->entityTypes[$tableIdentifier] = $this->retrieveTableDescription($possibleEntityTypeIdentifier);
                return $this->entityTypes[$tableIdentifier];
            }
        }

        $indexes = $this->schema->listIndexesForTable($tableIdentifier)->fetchAll();
        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'PRIMARY') {
                $this->entityTypes[$tableIdentifier]->identifier[] = $index['Column_name'];
            }
        }

        $columns = $this->schema->listColumnsForTable($tableIdentifier)->fetchAll();
        foreach ($columns as $column) {
            if ($column['Extra'] === 'auto_increment') {
                continue;
            } elseif ($column['Null'] === 'NO') {
                $this->entityTypes[$tableIdentifier]->requiredAttributeIdentifiers[] = $column['Field'];
            }
        }

        $foreignKeys = $this->schema->listForeignKeys($tableIdentifier)->fetchAll();
        foreach ($foreignKeys as $foreignKey) {
            $this->entityTypes[$tableIdentifier]->addForeignKeyConstraint($foreignKey['CONSTRAINT_NAME'], $foreignKey['COLUMN_NAME'], $foreignKey['REFERENCED_TABLE_NAME'], $foreignKey['REFERENCED_COLUMN_NAME']);
            $this->retrieveTableDescription($foreignKey['REFERENCED_TABLE_NAME'])->addForeignKeyConstraint($foreignKey['CONSTRAINT_NAME'], $foreignKey['REFERENCED_COLUMN_NAME'], $tableIdentifier, $foreignKey['COLUMN_NAME']);
        }

        return $this->entityTypes[$tableIdentifier];
    }

    public function current()
    {
        return $this->retrieveTableDescription(key($this->entityTypes));
    }

    public function next()
    {
        return next($this->entityTypes);
    }

    public function key()
    {
        return key($this->entityTypes);
    }

    public function valid()
    {
        return $this->key() !== null;
    }

    public function rewind()
    {
        return reset($this->entityTypes);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->entityTypes);
    }

    public function offsetGet($offset)
    {
        return $this->retrieveTableDescription($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->entityTypes[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->entityTypes[$offset]);
    }
}