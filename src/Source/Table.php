<?php
namespace ActiveRecord\Source;

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

    private function describeMethod(bool $static, array $parameters, array $body) : array {
        return [
            'static' => $static,
            'parameters' => $parameters,
            'body' => $body
        ];
    }

    private function describePrimaryKeyMethod(array $primaryKeyWhere) {
        if (count($primaryKeyWhere) === 0) {
            $array = '[]';
        } else {
            $array = '[\'' . join('\', \'', $primaryKeyWhere) . '\']';
        }

        return $this->describeMethod(false, [], [
            'return ' . $array . ';'
        ]);
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

        $references = [];
        foreach ($dbalSchemaTable->getForeignKeys() as $foreignKeyIdentifier => $foreignKey) {
            $references[join('', array_map('ucfirst', explode('_', $foreignKeyIdentifier)))] = [
                'table' => $foreignKey->getForeignTableName(),
                'where' => array_combine($foreignKey->getForeignColumns(), $foreignKey->getLocalColumns())
            ];
        }

        if (count($references) === 0) {
            $referencesLines = ['return [];'];
        } else {
            $referencesLines = explode(PHP_EOL, \var_export_short($references, true));
            $referencesLines[0] = 'return ' . $referencesLines[0];
            $referencesLines[count($referencesLines) - 1] .= ';';
        }

        $methods = [
            'identifier' => $this->describePrimaryKeyMethod($primaryKeyColumns),
            'references' => $this->describeMethod(false, [], $referencesLines)
        ];
        
        return [
            'identifier' => $this->namespace . $dbalSchemaTable->getName(),
            'recordIdentifier' => $primaryKeyColumns,
            'interfaces' => ['\\ActiveRecord\\MetaRecord'],
            'methods' => $methods
        ];
    }

    private function describeView(\Doctrine\DBAL\Schema\View $dbalSchemaView) : array {
        $methods = [
            'identifier' => $this->describePrimaryKeyMethod([]),
            'references' => $this->describeMethod(false, [], ['return [];'])
        ];

        return [
            'identifier' => $this->namespace . $dbalSchemaView->getName(),
            'recordIdentifier' => [],
            'interfaces' => ['\\ActiveRecord\\MetaRecord'],
            'methods' => $methods
        ];
    }
    
}