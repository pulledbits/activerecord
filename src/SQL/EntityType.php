<?php
namespace pulledbits\ActiveRecord\SQL;

class EntityType
{
    private $schema;
    private $entityTypeIdentifier;

    private $identifier = [];
    private $requiredAttributeIdentifiers = [];
    private $references = [];

    public function __construct(\pulledbits\ActiveRecord\Schema $schema, $tableIdentifier)
    {
        $this->schema = $schema;
        $this->entityTypeIdentifier = $tableIdentifier;

        $indexes = $schema->listIndexesForTable($tableIdentifier)->fetchAll();
        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'PRIMARY') {
                $this->identifier[] = $index['Column_name'];
            }
        }

        $columns = $schema->listColumnsForTable($tableIdentifier)->fetchAll();
        foreach ($columns as $column) {
            if ($column['Extra'] === 'auto_increment') {
                continue;
            } elseif ($column['Null'] === 'NO') {
                $this->requiredAttributeIdentifiers[] = $column['Field'];
            }
        }

        $foreignKeys = $schema->listForeignKeys($tableIdentifier)->fetchAll();
        foreach ($foreignKeys as $foreignKey) {
            $this->addForeignKeyConstraint($foreignKey['CONSTRAINT_NAME'], $foreignKey['COLUMN_NAME'], $foreignKey['REFERENCED_TABLE_NAME'], $foreignKey['REFERENCED_COLUMN_NAME']);
        }
    }

    public function primaryKey(array $values) {
        $sliced = [];
        foreach ($values as $key => $value) {
            if (in_array($key, $this->identifier, true)) {
                $sliced[$key] = $value;
            }
        }
        return $sliced;
    }

    public function calculateMissingValues(array $values) : array {
        $missing = [];
        foreach ($this->requiredAttributeIdentifiers as $requiredColumnIdentifier) {
            if (array_key_exists($requiredColumnIdentifier, $values) === false) {
                $missing[] = $requiredColumnIdentifier;
                break;
            } elseif ($values[$requiredColumnIdentifier] === null) {
                $missing[] = $requiredColumnIdentifier;
                break;
            }
        }
        return $missing;
    }

    private function addForeignKeyConstraint(string $constraintName, string $columnName, string $referencedTableName, string $referencedColumnName) {
        $fkIdentifier = join('', array_map('ucfirst', explode('_', $constraintName)));
        if (array_key_exists($fkIdentifier, $this->references)) {
            $this->references[$fkIdentifier]['conditions'][$referencedColumnName] = $columnName;
        } else {
            $this->references[$fkIdentifier] = [
                'entityTypeIdentifier' => $referencedTableName,
                'conditions' => [$referencedColumnName => $columnName]
            ];
        }
    }

    public function update(array $values, array $conditions) : int
    {
        return $this->schema->update($this->entityTypeIdentifier, $values, $conditions);
    }


    public function delete(array $conditions) : int
    {
        return $this->schema->delete($this->entityTypeIdentifier, $conditions);
    }

    public function create(array $values) : int
    {
        $missing = $this->calculateMissingValues($values);
        if (count($missing) > 0) {
            trigger_error('Required values are missing: ' . join(', ', $missing), E_USER_ERROR);
        }
        return $this->schema->create($this->entityTypeIdentifier, $values);
    }

    private function mergeValuesIntoConditions(array $referenceConditions, array $values, array $conditions) {
        foreach ($referenceConditions as $referencedColumnIdentifier => $localColumnIdentifier) {
            if (array_key_exists($localColumnIdentifier, $values)) {
                $conditions[$referencedColumnIdentifier] = $values[$localColumnIdentifier];
            } else {
                $conditions[$referencedColumnIdentifier] = null;
            }
        }
        return $conditions;
    }

    private function findReference(string $referenceIdentifier) : array {
        if (array_key_exists($referenceIdentifier, $this->references) === false) {
            trigger_error('Reference does not exist `' . $referenceIdentifier . '`', E_USER_ERROR);
        }
        return $this->references[$referenceIdentifier];
    }

    public function fetchBy(string $referenceIdentifier, array $values, array $conditions) : array {
        $reference = $this->findReference($referenceIdentifier);
        $conditions = $this->mergeValuesIntoConditions($reference['conditions'], $values, $conditions);
        return $this->schema->read($reference['entityTypeIdentifier'], [], $conditions);
    }

    public function referenceBy(string $referenceIdentifier, array $values, array $conditions) : \pulledbits\ActiveRecord\Record {
        $reference = $this->findReference($referenceIdentifier);
        $conditions = $this->mergeValuesIntoConditions($reference['conditions'], $values, $conditions);
        $this->schema->create($reference['entityTypeIdentifier'], $conditions);
        $records = $this->schema->read($reference['entityTypeIdentifier'], [], $conditions);
        return $records[0];
    }
}