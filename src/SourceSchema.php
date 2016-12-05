<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:45
 */

namespace ActiveRecord;


use Doctrine\DBAL\Schema\AbstractSchemaManager;

class SourceSchema
{
    /**
     * @var Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    private $schemaManager;

    public function __construct(AbstractSchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    public function describe(string $namespace)
    {
        if (substr($namespace, -1) != "\\") {
            $namespace .= "\\";
        }

        $tableClasses = [];

        foreach ($this->schemaManager->listTables() as $table) {
            $sourceTable = new SourceTable($table);
            $tableClasses[$table->getName()] = $sourceTable->describe($namespace);
        }

        return [
            'identifier' => $namespace . 'Schema',
            'tableClasses' => $tableClasses
        ];
    }
}