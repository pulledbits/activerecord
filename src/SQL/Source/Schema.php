<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:45
 */

namespace pulledbits\ActiveRecord\SQL\Source;


use Doctrine\DBAL\Schema\AbstractSchemaManager;

class Schema
{
    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    private $schemaManager;

    public function __construct(AbstractSchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    public function describe(Table $sourceTable)
    {
        $tables = [];
        foreach ($this->schemaManager->listTables() as $table) {
            $tables[$table->getName()] = $sourceTable->describe($table);
        }

        $reversedLinkedTables = $tables;
        foreach ($tables as $tableName => $recordClassDescription) {
            foreach ($recordClassDescription['references'] as $referenceIdentifier => $reference) {
                $reversedLinkedTables[$reference['table']]['references'][$referenceIdentifier] = [
                    'table' => $tableName,
                    'where' => array_flip($reference['where'])
                ];
            }
        }
        $tables = $reversedLinkedTables;

        foreach ($this->schemaManager->listViews() as $view) {
            $viewIdentifier = $view->getName();
            if (strpos($viewIdentifier, '_') > 0) {
                $possibleEntityTypeIdentifier = substr($viewIdentifier, 0, strpos($viewIdentifier, '_'));
                if (array_key_exists($possibleEntityTypeIdentifier, $tables)) {
                    $tables[$viewIdentifier] = [
                        'entityTypeIdentifier' => $possibleEntityTypeIdentifier
                    ];
                    continue;
                }
            }

            $tables[$viewIdentifier] = [
                'identifier' => [],
                'requiredAttributeIdentifiers' => [],
                'references' => []
            ];
        }

        return $tables;
    }
}