<?php
namespace pulledbits\ActiveRecord\SQL\Source;

final class Table
{
    /**
     * Mimicks overloading
     * @param \Doctrine\DBAL\Schema\AbstractAsset $dbalSchemaAsset
     * @return array
     */
    public function describe(\Doctrine\DBAL\Schema\AbstractAsset $dbalSchemaAsset) : array {
        if ($dbalSchemaAsset instanceof \Doctrine\DBAL\Schema\Table) {
            $description = $this->describeTable($dbalSchemaAsset);
        } elseif ($dbalSchemaAsset instanceof \Doctrine\DBAL\Schema\View) {
            $description = $this->describeView($dbalSchemaAsset);
        } else {
            $description = [];
        }

        return $description + [
            'identifier' => [],
            'requiredAttributeIdentifiers' => [],
            'references' => []
        ];
    }

    private function describeTable(\Doctrine\DBAL\Schema\Table $dbalSchemaTable) : array {
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
            $references[join('', array_map('ucfirst', explode('_', $foreignKeyIdentifier)))] = [
                'table' => $foreignKey->getForeignTableName(),
                'where' => array_combine($foreignKey->getForeignColumns(), $foreignKey->getLocalColumns())
            ];
        }

        return [
            'identifier' => $primaryKeyColumns,
            'requiredAttributeIdentifiers' => $requiredAttributeIdentifiers,
            'references' => $references
        ];
    }

    private function describeView(\Doctrine\DBAL\Schema\View $dbalSchemaView) : array {
        return [];
    }
    
}