<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 15-12-16
 * Time: 15:19
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

    public function select(string $tableIdentifer, array $whereParameters)
    {
        $namedParameters = $where = [];
        foreach ($whereParameters as $localColumn => $value) {
            $namedParameter = null;
            $where[] = $this->whereEquals($localColumn, $namedParameter);
            $namedParameters[$namedParameter] = $value;
        }
        $query = "SELECT * FROM " . $tableIdentifer;
        if (count($where) > 0) {
           $query .= " WHERE " . join(" AND ", $where);
        }
        $statement = $this->connection->prepare($query);
        foreach ($namedParameters as $namedParameter => $value) {
            $statement->bindParam($namedParameter, $value, \PDO::PARAM_STR);
        }
        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_CLASS, $this->targetNamespace . '\\Record\\' . $tableIdentifer, [$this]);
    }
}