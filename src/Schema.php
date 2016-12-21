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

    public function prepareParameters(string $type, array $parameters) {
        $namedParameters = $sql = [];
        foreach ($parameters as $localColumn => $value) {
            $namedParameter = ":" . sha1($type . '_' . $localColumn);
            $sql[$localColumn] = $localColumn . " = " . $namedParameter;
            $namedParameters[$namedParameter] = $value;
        }
        return [$sql, $namedParameters];
    }

    public function prepareFields(array $fields) {
        $preparedFields = [];
        foreach ($fields as $fieldAlias => $columnIdentifier) {
            $preparedFields[] = $columnIdentifier . ' AS ' . $this->transformColumnToProperty($columnIdentifier);
        }
        return $preparedFields;
    }

    public function execute(string $query, array $namedParameters) : \PDOStatement
    {
        $statement = $this->connection->prepare($query);
        foreach ($namedParameters as $namedParameter => $value) {
            $statement->bindParam($namedParameter, $value, \PDO::PARAM_STR);
        }
        $statement->execute();
        return $statement;
    }
}