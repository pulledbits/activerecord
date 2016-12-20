<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:08
 */

namespace ActiveRecord;


class Schema
{
    const COLUMN_PROPERTY_ESCAPE = '_';

    /**
     * @var string
     */
    private $targetNamespace;

    /**
     * @var \PDO
     */
    private $connection;

    public function __construct(string $targetNamespace, \PDO $connection) {
        $this->targetNamespace = $targetNamespace;
        $this->connection = $connection;
    }

    public function transformColumnToProperty($columnIdentifier)
    {
        return self::COLUMN_PROPERTY_ESCAPE . $columnIdentifier;
    }

    public function transformTableIdentifierToRecordClassIdentifier($tableIdentfier) {
        return $this->targetNamespace . '\\' . $tableIdentfier;
    }
}