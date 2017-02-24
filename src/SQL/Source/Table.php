<?php
namespace pulledbits\ActiveRecord\SQL\Source;

final class Table
{
    /**
     * @param \Doctrine\DBAL\Schema\AbstractAsset $dbalSchemaTable
     * @return array
     */
    public function describe(\Doctrine\DBAL\Schema\Table $dbalSchemaTable, \pulledbits\ActiveRecord\Source\GeneratorGeneratorFactory $factory) : array {
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
            $references[join('', array_map('ucfirst', explode('_', $foreignKeyIdentifier)))] = $factory->makeReference($foreignKey->getForeignTableName(), array_combine($foreignKey->getForeignColumns(), $foreignKey->getLocalColumns()));
        }

        return [
            'identifier' => $primaryKeyColumns,
            'requiredAttributeIdentifiers' => $requiredAttributeIdentifiers,
            'references' => $references
        ];
    }
    
}