<?php
namespace pulledbits\ActiveRecord\SQL\Meta;


use Doctrine\DBAL\Schema\AbstractSchemaManager;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

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
        return new ConfiguratorFactory(new \pulledbits\ActiveRecord\Source\RecordConfiguratorGeneratorFactory($this), $targetDirectory);
    }

    public function describeTable(string $tableIdentifier) : RecordConfiguratorGenerator
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
                if (array_key_exists($reference['table'], $reversedLinkedTables) === false) {
                    $reversedLinkedTables[$reference['table']] = ['identifier' => [], 'requiredAttributeIdentifiers' => [], 'references' => []];
                }
                $reversedLinkedTables[$reference['table']]['references'][$referenceIdentifier] = $sourceTable->makeReference($tableName, array_flip($reference['where']));
            }
        }
        $tables = array_map(function(array $tableDescription) {
            return new RecordConfiguratorGenerator\Record($tableDescription);
        }, $reversedLinkedTables);

        foreach ($this->schemaManager->listViews() as $view) {
            $viewIdentifier = $view->getName();

            $tables[$viewIdentifier] = new RecordConfiguratorGenerator\Record([
                'identifier' => [],
                'requiredAttributeIdentifiers' => [],
                'references' => []
            ]);

            $underscorePosition = strpos($viewIdentifier, '_');
            if ($underscorePosition < 1) {
                continue;
            }

            $possibleEntityTypeIdentifier = substr($viewIdentifier, 0, $underscorePosition);
            if (array_key_exists($possibleEntityTypeIdentifier, $tables) === false) {
                continue;
            }

            $tables[$viewIdentifier] = new RecordConfiguratorGenerator\WrappedEntity($possibleEntityTypeIdentifier);
        }

        return $tables;
    }
}