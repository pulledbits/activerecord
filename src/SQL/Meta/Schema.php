<?php
namespace pulledbits\ActiveRecord\SQL\Meta;


use Doctrine\DBAL\Schema\AbstractSchemaManager;

final class Schema implements \pulledbits\ActiveRecord\Source\Schema
{
    private $schemaManager;

    public function __construct(AbstractSchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
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
    static function fromDatabaseURL(string $url) : self
    {
        $parsedUrl = parse_url($url);
        return self::fromPDO(new \PDO($parsedUrl['scheme'] . ':dbname=' . substr($parsedUrl['path'], 1), $parsedUrl['user'], $parsedUrl['pass'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'')));
    }

    public function createConfigurator(string $targetDirectory)
    {
        return new Configurator(new \pulledbits\ActiveRecord\Source\ConfiguratorGeneratorFactory($this), $targetDirectory);
    }

    public function describeTable(string $tableIdentifier) : array
    {
        $table = $this->describeTables();
        return $table[$tableIdentifier];
    }

    public function describeTables()
    {
        $sourceTable = new \pulledbits\ActiveRecord\SQL\Meta\Table();

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