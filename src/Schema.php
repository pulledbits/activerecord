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

    private function prepare(string $query, array $namedParameters) : \PDOStatement
    {
        $statement = $this->connection->prepare($query);
        foreach ($namedParameters as $namedParameter => $value) {
            $statement->bindParam($namedParameter, $value, \PDO::PARAM_STR);
        }
        return $statement;
    }

    private function prepareParameters(string $type, array $parameters) {
        $namedParameters = $sql = [];
        foreach ($parameters as $localColumn => $value) {
            $namedParameter = ":" . sha1($type . '_' . $localColumn);
            $sql[] = $localColumn . " = " . $namedParameter;
            $namedParameters[$namedParameter] = $value;
        }
        return [$sql, $namedParameters];
    }

    public function select(string $tableIdentifer, array $whereParameters)
    {
        list($where, $namedParameters) = $this->prepareParameters('where', $whereParameters);
        $query = "SELECT * FROM " . $tableIdentifer;
        if (count($where) > 0) {
           $query .= " WHERE " . join(" AND ", $where);
        }
        $statement = $this->prepare($query, $namedParameters);
        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_CLASS, $this->targetNamespace . '\\Record\\' . $tableIdentifer, [$this]);
    }

    public function update(string $tableIdentifer, array $setParameters, array $whereParameters) {
        list($set, $setNamedParameters) = $this->prepareParameters('set', $setParameters);
        list($where, $whereNamedParameters) = $this->prepareParameters('where', $whereParameters);

        $query = "UPDATE " . $tableIdentifer . " SET " . join(", ", $set);
        if (count($where) > 0) {
           $query .= " WHERE " . join(" AND ", $where);
        }

        $statement = $this->prepare($query, array_merge($setNamedParameters, $whereNamedParameters));
        $statement->execute();
        return $statement->rowCount();
    }
}