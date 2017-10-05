<?php
namespace pulledbits\ActiveRecord\Source\SQL;


use Doctrine\DBAL\Schema\AbstractSchemaManager;

final class Schema implements \pulledbits\ActiveRecord\Source\Schema
{
    private $schemaManager;

    public function __construct(AbstractSchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    static function fromDatabaseURL(string $dburl) : Schema
    {
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = [
            'url' => $dburl
        ];
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        return new self($conn->getSchemaManager());
    }

    static function fromPDO(\PDO $pdo) : Schema
    {
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = [
            'pdo' => $pdo
        ];
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        return new self($conn->getSchemaManager());
    }

    public function describeTable(\pulledbits\ActiveRecord\Source\Table $sourceTable, string $tableIdentifier) : array
    {
        $table = $this->describeTables($sourceTable);
        return $table[$tableIdentifier];
    }

    public function describeTables(\pulledbits\ActiveRecord\Source\Table $sourceTable)
    {
        $tables = [];
        foreach ($this->schemaManager->listTables() as $table) {
            $tables[$table->getName()] = $sourceTable->describe($table);
        }

        $reversedLinkedTables = $tables;
        foreach ($tables as $tableName => $recordClassDescription) {
            foreach ($recordClassDescription['references'] as $referenceIdentifier => $reference) {
                $reversedLinkedTables[$reference['table']]['references'][$referenceIdentifier] = $sourceTable->makeReference($tableName, array_flip($reference['where']));
            }
        }
        $tables = $reversedLinkedTables;

        foreach ($this->schemaManager->listViews() as $view) {
            $viewIdentifier = $view->getName();

            $tables[$viewIdentifier] = [
                'identifier' => [],
                'requiredAttributeIdentifiers' => [],
                'references' => []
            ];

            $underscorePosition = strpos($viewIdentifier, '_');
            if ($underscorePosition < 1) {
                continue;
            }

            $possibleEntityTypeIdentifier = substr($viewIdentifier, 0, $underscorePosition);
            if (array_key_exists($possibleEntityTypeIdentifier, $tables) === false) {
                continue;
            }

            $tables[$viewIdentifier] = [
                'entityTypeIdentifier' => $possibleEntityTypeIdentifier
            ];
        }

        return $tables;
    }
}