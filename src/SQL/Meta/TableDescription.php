<?php
namespace pulledbits\ActiveRecord\SQL\Meta;


use pulledbits\ActiveRecord\Struct;

class TableDescription extends Struct
{
    public $identifier = [];
    public $requiredAttributeIdentifiers = [];
    public $references = [];

    static function makeFromDBALTable(\Doctrine\DBAL\Schema\Table $dbalSchemaTable) : self
    {
        $description = new self([], [], []);

        if ($dbalSchemaTable->hasPrimaryKey()) {
            $description->identifier = $dbalSchemaTable->getPrimaryKeyColumns();
        }

        foreach ($dbalSchemaTable->getColumns() as $columnIdentifier => $column) {
            if ($column->getAutoincrement()) {
                continue;
            } elseif ($column->getNotnull()) {
                $description->requiredAttributeIdentifiers[] = $columnIdentifier;
            }
        }

        foreach ($dbalSchemaTable->getForeignKeys() as $foreignKeyIdentifier => $foreignKey) {
            $description->references[join('', array_map('ucfirst', explode('_', $foreignKeyIdentifier)))] = self::makeReference($foreignKey->getForeignTableName(), array_combine($foreignKey->getForeignColumns(), $foreignKey->getLocalColumns()));
        }

        return $description;
    }

    static function makeReference(string $entityTypeIdentifier, array $conditions) : array
    {
        return [
            'table' => $entityTypeIdentifier,
            'where' => $conditions
        ];
    }
}