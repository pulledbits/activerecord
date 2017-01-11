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

    public function transformTableIdentifierToRecordClassIdentifier($tableIdentfier) {
        return $this->targetNamespace . '\\' . $tableIdentfier;
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