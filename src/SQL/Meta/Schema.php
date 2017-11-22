<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

final class Schema implements \pulledbits\ActiveRecord\Source\Schema
{
    private $cachedTableDescriptions;

    public function __construct(array $prototypeTables, array $dbalViews)
    {
        $this->cachedTableDescriptions = [];
        foreach ($prototypeTables as $prototypeTableIdentifier => $prototypeTable) {
            $this->cachedTableDescriptions[$prototypeTableIdentifier] = new RecordConfiguratorGenerator\Record($prototypeTable);
        }

        foreach ($dbalViews as $viewIdentifier => $viewSQL) {
            $this->cachedTableDescriptions[$viewIdentifier] = new RecordConfiguratorGenerator\Record(['identifier' => [], 'requiredAttributeIdentifiers' => [], 'references' => []]);

            $underscorePosition = strpos($viewIdentifier, '_');
            if ($underscorePosition < 1) {
                continue;
            }

            $possibleEntityTypeIdentifier = substr($viewIdentifier, 0, $underscorePosition);
            if (array_key_exists($possibleEntityTypeIdentifier, $this->cachedTableDescriptions) === false) {
                continue;
            }

            $this->cachedTableDescriptions[$viewIdentifier] = new RecordConfiguratorGenerator\WrappedEntity($possibleEntityTypeIdentifier);
        }
    }

    static function fromDatabaseURL(string $url) : self
    {
        $parsedUrl = parse_url($url);
        return self::fromPDO(new \PDO($parsedUrl['scheme'] . ':dbname=' . substr($parsedUrl['path'], 1), $parsedUrl['user'], $parsedUrl['pass'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'')));
    }

    static function fromPDO(\PDO $pdo) : Schema
    {
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = [
            'pdo' => $pdo
        ];
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        return self::fromSchemaManager($conn->getSchemaManager());
    }

    static function fromSchemaManager(AbstractSchemaManager $schemaManager) {
        $sourceTable = new \pulledbits\ActiveRecord\SQL\Meta\Table();
        $tables = [];
        foreach ($schemaManager->listTables() as $dbalTable) {
            $tables[$dbalTable->getName()] = $sourceTable->describe($dbalTable);
        }
        $prototypeTables = $tables;
        foreach ($tables as $tableName => $recordClassDescription) {
            foreach ($recordClassDescription['references'] as $referenceIdentifier => $reference) {
                if (array_key_exists($reference['table'], $prototypeTables) === false) {
                    $prototypeTables[$reference['table']] = ['identifier' => [], 'requiredAttributeIdentifiers' => [], 'references' => []];
                }
                $prototypeTables[$reference['table']]['references'][$referenceIdentifier] = $sourceTable->makeReference($tableName, array_flip($reference['where']));
            }
        }

        $prototypeViews = [];
        foreach ($schemaManager->listViews() as $dbalView) {
            $prototypeViews[$dbalView->getName()] = $dbalView->getSql();
        }

        return new self($prototypeTables, $prototypeViews);
    }

    public function createConfigurator(string $targetDirectory)
    {
        return new ConfiguratorFactory(new \pulledbits\ActiveRecord\Source\RecordConfiguratorGeneratorFactory($this), $targetDirectory);
    }

    public function describeTable(string $tableIdentifier) : RecordConfiguratorGenerator
    {
        return $this->cachedTableDescriptions[$tableIdentifier];
    }
}