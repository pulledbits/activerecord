<?php
namespace ActiveRecord\SQL\Source;

final class Table
{

    /**
     * @var string
     */
    private $namespace;

    /**
     * Table constructor.
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
        if (substr($this->namespace, -1) != "\\") {
            $this->namespace .= "\\";
        }
    }

    /**
     * Mimicks overloading
     * @param \Doctrine\DBAL\Schema\AbstractAsset $dbalSchemaAsset
     * @return array
     */
    public function describe(\Doctrine\DBAL\Schema\AbstractAsset $dbalSchemaAsset) : array {
        if ($dbalSchemaAsset instanceof \Doctrine\DBAL\Schema\Table) {
            return $this->describeTable($dbalSchemaAsset);
        } elseif ($dbalSchemaAsset instanceof \Doctrine\DBAL\Schema\View) {
            return $this->describeView($dbalSchemaAsset);
        }
    }

    private function describeTable(\Doctrine\DBAL\Schema\Table $dbalSchemaTable) : array {
        $primaryKeyColumns = [];
        if ($dbalSchemaTable->hasPrimaryKey()) {
            $primaryKeyColumns = $dbalSchemaTable->getPrimaryKeyColumns();
        }

        $requiredColumnIdentifiers = [];
        foreach ($dbalSchemaTable->getColumns() as $columnIdentifier => $column) {
            if ($column->getNotnull()) {
                $requiredColumnIdentifiers[] = $columnIdentifier;
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
            'requiredColumnIdentifiers' => $requiredColumnIdentifiers,
            'references' => $references
        ];
    }

    private function describeView(\Doctrine\DBAL\Schema\View $dbalSchemaView) : array {
        return [
            'identifier' => [],
            'references' => []
        ];
    }
    
}