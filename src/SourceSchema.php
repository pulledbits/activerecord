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

    public function describe()
    {
        $classes = [];

        foreach ($this->schemaManager->listTables() as $table) {
            $tableName = $table->getName();
            $classes[$tableName] = ['identifier' => $tableName];
        }

        return [
            'classes' => $classes
        ];
    }
}