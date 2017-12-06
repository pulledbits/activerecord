<?php


namespace pulledbits\ActiveRecord;


use pulledbits\ActiveRecord\SQL\Meta\TableDescription;

class EntityTypes implements \Iterator, \ArrayAccess
{
    private $schema;
    private $entityTypes;

    public function __construct(Schema $schema, Result $result)
    {
        $this->schema = $schema;
        $tables = [];
        foreach ($result->fetchAll() as $baseTable) {
            $tableIdentifier = array_shift($baseTable);

            $tables[$tableIdentifier] = new TableDescription([], [], []);

            $indexes = $schema->listIndexesForTable($tableIdentifier)->fetchAll();
            foreach ($indexes as $index) {
                if ($index['Key_name'] === 'PRIMARY') {
                    $tables[$tableIdentifier]->identifier[] = $index['Column_name'];
                }
            }

            $columns = $schema->listColumnsForTable($tableIdentifier)->fetchAll();
            foreach ($columns as $column) {
                if ($column['Extra'] === 'auto_increment') {
                    continue;
                } elseif ($column['Null'] === 'NO') {
                    $tables[$tableIdentifier]->requiredAttributeIdentifiers[] = $column['Field'];
                }
            }

            $foreignKeys = $schema->listForeignKeys($tableIdentifier)->fetchAll();
            foreach ($foreignKeys as $foreignKey) {
                $tables[$tableIdentifier]->addForeignKeyConstraint($foreignKey['CONSTRAINT_NAME'], $foreignKey['COLUMN_NAME'], $foreignKey['REFERENCED_TABLE_NAME'], $foreignKey['REFERENCED_COLUMN_NAME']);
            }
        }

        $this->entityTypes = $tables;
        foreach ($tables as $tableName => $recordClassDescription) {
            foreach ($recordClassDescription->references as $referenceIdentifier => $reference) {
                foreach ($reference['where'] as $localColumnIdentifier => $referencedColumnIdentifier) {
                    $this->entityTypes[$reference['table']]->addForeignKeyConstraint($referenceIdentifier, $localColumnIdentifier, $tableName, $referencedColumnIdentifier);
                }
            }
        }


        $fullViews = $schema->listViews()->fetchAll();
        foreach ($fullViews as $fullView) {
            $viewIdentifier = $fullView['TABLE_NAME'];
            $underscorePosition = strpos($viewIdentifier, '_');
            if ($underscorePosition < 1) {
                continue;
            }
            $possibleEntityTypeIdentifier = substr($viewIdentifier, 0, $underscorePosition);
            if (array_key_exists($possibleEntityTypeIdentifier, $this->entityTypes) === false) {
                continue;
            }
            $this->entityTypes[$viewIdentifier] = $this->entityTypes[$possibleEntityTypeIdentifier];
        }
    }

    public function current()
    {
        return current($this->entityTypes);
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
        if (array_key_exists($offset, $this->entityTypes) === false) {
            return new TableDescription();
        }
        return $this->entityTypes[$offset];
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