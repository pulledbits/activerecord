<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:45
 */

namespace ActiveRecord\SQL\Source;


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
        foreach (array_merge($this->schemaManager->listTables(), $this->schemaManager->listViews()) as $table) {
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
        return $reversedLinkedTables;
    }
}