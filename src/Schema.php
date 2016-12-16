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
            $sql[$localColumn] = $localColumn . " = " . $namedParameter;
            $namedParameters[$namedParameter] = $value;
        }
        return [$sql, $namedParameters];
    }

    public function select(string $tableIdentifer, array $fields, array $whereParameters)
    {
        list($where, $namedParameters) = $this->prepareParameters('where', $whereParameters);

        $preparedFields = [];
        foreach ($fields as $fieldAlias => $columnIdentifier) {
            if (is_numeric($fieldAlias)) {
                $preparedFields[] = $columnIdentifier;
            } else {
                $preparedFields[] = $columnIdentifier . ' AS ' . $fieldAlias;
            }
        }
        $query = "SELECT " . join(', ', $preparedFields) . " FROM " . $tableIdentifer;
        if (count($where) > 0) {
           $query .= " WHERE " . join(" AND ", $where);
        }
        $statement = $this->prepare($query, $namedParameters);
        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_CLASS, $this->targetNamespace . '\\' . $tableIdentifer, [$this]);
    }

    public function insert(string $tableIdentifer, array $values) {
        list($insertValues, $insertNamedParameters) = $this->prepareParameters('values', $values);

        $query = "INSERT INTO " . $tableIdentifer . " (" . join(', ', array_keys($insertValues)) . ") VALUES (" . join(', ', array_keys($insertNamedParameters)) . ")";
        $statement = $this->prepare($query, $insertNamedParameters);
        $statement->execute();
        return $this->select($tableIdentifer, array_keys($insertValues), $values)[0];
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

    public function delete(string $tableIdentifer, array $whereParameters) {
        list($where, $whereNamedParameters) = $this->prepareParameters('where', $whereParameters);

        $query = "DELETE FROM " . $tableIdentifer;
        if (count($where) > 0) {
            $query .= " WHERE " . join(" AND ", $where);
        }

        $statement = $this->prepare($query, $whereNamedParameters);
        $statement->execute();
        return $statement->rowCount();
    }
}