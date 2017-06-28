<?php
namespace pulledbits\ActiveRecord\SQL\Source;

final class Table
{
    public function describe(\Doctrine\DBAL\Schema\Table $dbalSchemaTable) : array {
        $description = [
            'identifier' => [],
            'requiredAttributeIdentifiers' => [],
            'references' => []
        ];

        if ($dbalSchemaTable->hasPrimaryKey()) {
            $description['identifier'] = $dbalSchemaTable->getPrimaryKeyColumns();
        }

        foreach ($dbalSchemaTable->getColumns() as $columnIdentifier => $column) {
            if ($column->getAutoincrement()) {
                continue;
            } elseif ($column->getNotnull()) {
                $description['requiredAttributeIdentifiers'][] = $columnIdentifier;
            }
        }

        foreach ($dbalSchemaTable->getForeignKeys() as $foreignKeyIdentifier => $foreignKey) {
            $description['references'][join('', array_map('ucfirst', explode('_', $foreignKeyIdentifier)))] = $this->makeReference($foreignKey->getForeignTableName(), array_combine($foreignKey->getForeignColumns(), $foreignKey->getLocalColumns()));
        }

        return $description;
    }

    public function makeReference(string $entityTypeIdentifier, array $conditions) : array
    {
        return [
            'table' => $entityTypeIdentifier,
            'where' => $conditions
        ];
    }
}