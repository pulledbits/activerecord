<?php
namespace pulledbits\ActiveRecord\SQL\Source;

final class Table
{
    /**
     * @param \Doctrine\DBAL\Schema\AbstractAsset $dbalSchemaTable
     * @return array
     */
    public function describe(\Doctrine\DBAL\Schema\Table $dbalSchemaTable) : array {
        $primaryKeyColumns = [];
        if ($dbalSchemaTable->hasPrimaryKey()) {
            $primaryKeyColumns = $dbalSchemaTable->getPrimaryKeyColumns();
        }

        $requiredAttributeIdentifiers = [];
        foreach ($dbalSchemaTable->getColumns() as $columnIdentifier => $column) {
            if ($column->getAutoincrement()) {
                continue;
            } elseif ($column->getNotnull()) {
                $requiredAttributeIdentifiers[] = $columnIdentifier;
            }
        }
        $references = [];
        foreach ($dbalSchemaTable->getForeignKeys() as $foreignKeyIdentifier => $foreignKey) {
            $references[join('', array_map('ucfirst', explode('_', $foreignKeyIdentifier)))] = $this->makeReference($foreignKey->getForeignTableName(), array_combine($foreignKey->getForeignColumns(), $foreignKey->getLocalColumns()));
        }

        return [
            'identifier' => $primaryKeyColumns,
            'requiredAttributeIdentifiers' => $requiredAttributeIdentifiers,
            'references' => $references
        ];
    }

    public function makeReference(string $entityTypeIdentifier, array $conditions) : array
    {
        return [
            'table' => $entityTypeIdentifier,
            'where' => $conditions
        ];
    }
}