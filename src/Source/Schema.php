<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:45
 */

namespace ActiveRecord\Source;


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

    public function describe(string $namespace)
    {
        if (substr($namespace, -1) != "\\") {
            $namespace .= "\\";
        }

        $recordClasses = [];

        foreach ($this->schemaManager->listTables() as $table) {
            $sourceTable = new Table($table);
            $recordClasses[$table->getName()] = $sourceTable->describe($namespace . 'Record\\' );
        }

        return [
            'recordClasses' => $recordClasses
        ];
    }
}