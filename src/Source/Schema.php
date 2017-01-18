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

    public function describe(Table $sourceTable, \Closure $tableHandler)
    {
        foreach ($this->schemaManager->listTables() as $table) {
            $tableHandler($table->getName(), $sourceTable->describe($table));
        }
    }
}