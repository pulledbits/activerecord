<?php
namespace pulledbits\ActiveRecord\SQL\Meta;


use pulledbits\ActiveRecord\Struct;

class TableDescription extends Struct
{
    public $identifier = [];
    public $requiredAttributeIdentifiers = [];
    public $references = [];

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