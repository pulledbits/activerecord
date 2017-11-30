<?php
namespace pulledbits\ActiveRecord\SQL\Meta;


use pulledbits\ActiveRecord\Struct;

class TableDescription extends Struct
{
    public $identifier = [];
    public $requiredAttributeIdentifiers = [];
    public $references = [];

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

    public function addForeignKeyConstraint(string $constraintName, string $columnName, string $referencedTableName, string $referencedColumnName) {
        $fkIdentifier = join('', array_map('ucfirst', explode('_', $constraintName)));
        if (array_key_exists($fkIdentifier, $this->references)) {
            $this->references[$fkIdentifier]['where'][$referencedColumnName] = $columnName;
        } else {
            $this->references[$fkIdentifier] = [
                'table' => $referencedTableName,
                'where' => [$referencedColumnName => $columnName]
            ];
        }
    }
}