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

        $sourceTable = new Table();
        foreach ($this->schemaManager->listTables() as $table) {
            $recordClasses[$table->getName()] = $sourceTable->describe($namespace . 'Record\\', $table);
        }

        return [
            'recordClasses' => $recordClasses
        ];
    }
}