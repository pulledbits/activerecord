<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:45
 */

namespace pulledbits\ActiveRecord\SQL\Source;


use Doctrine\DBAL\Schema\AbstractSchemaManager;
use pulledbits\ActiveRecord\Source\GeneratorGeneratorFactory;

final class Schema
{
    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    private $schemaManager;

    public function __construct(AbstractSchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    public function describe(Table $sourceTable, GeneratorGeneratorFactory $factory)
    {
        $tables = [];
        foreach ($this->schemaManager->listTables() as $table) {
            $tables[$table->getName()] = $sourceTable->describe($table, $factory);
        }

        $reversedLinkedTables = $tables;
        foreach ($tables as $tableName => $recordClassDescription) {
            foreach ($recordClassDescription['references'] as $referenceIdentifier => $reference) {
                $reversedLinkedTables[$reference['table']]['references'][$referenceIdentifier] = $factory->makeReference($tableName, array_flip($reference['where']));
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