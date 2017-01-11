<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 15-12-16
 * Time: 15:19
 */

namespace ActiveRecord;


class Table
{
    const COLUMN_PROPERTY_ESCAPE = '_';

    /**
     * @var \ActiveRecord\Schema
     */
    private $identifier;

    /**
     * @var \ActiveRecord\Schema
     */
    private $schema;

    public function __construct(string $identifier, Schema $schema) {
        $this->identifier = $identifier;
        $this->schema = $schema;
    }

    public function select(array $columnIdentifiers, array $whereParameters)
    {
        return $this->selectFrom($this->identifier, $columnIdentifiers, $whereParameters);
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

    private function makeWhereCondition(array $whereParameters, array &$namedParameters) {
        list($where, $namedParameters) = $this->prepareParameters('where', $whereParameters);
        if (count($where) === 0) {
            return "";
        }
        return " WHERE " . join(" AND ", $where);
    }


    public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters)
    {
        $namedParameters = [];
        $query = "SELECT " . join(', ', $columnIdentifiers) . " FROM " . $tableIdentifier . $this->makeWhereCondition($whereParameters, $namedParameters);
        $statement = $this->schema->execute($query, $namedParameters);

        $recordClassIdentifier = $this->schema->transformTableIdentifierToRecordClassIdentifier($this->identifier);
        $table = $this;
        return array_map(function(array $values) use ($recordClassIdentifier, $table) {
            return new $recordClassIdentifier($table, $values);
        }, $statement->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function insert(array $values) {
        list($insertValues, $namedParameters) = $this->prepareParameters('values', $values);
        $query = "INSERT INTO " . $this->identifier . " (" . join(', ', array_keys($insertValues)) . ") VALUES (" . join(', ', array_keys($namedParameters)) . ")";
        $this->schema->execute($query, $namedParameters);
        return $this->select(array_keys($values), $values);
    }

    public function update(array $setParameters, array $whereParameters) {
        list($set, $setNamedParameters) = $this->prepareParameters('set', $setParameters);

        $namedParameters = [];
        $query = "UPDATE " . $this->identifier . " SET " . join(", ", $set) . $this->makeWhereCondition($whereParameters, $namedParameters);

        $this->schema->execute($query, array_merge($setNamedParameters, $namedParameters));

        return $this->select(array_keys($setParameters), $whereParameters);
    }

    public function delete(array $whereParameters) {
        $records = $this->select(array_keys($whereParameters), $whereParameters);

        $namedParameters = [];
        $query = "DELETE FROM " . $this->identifier . $this->makeWhereCondition($whereParameters, $namedParameters);
        $this->schema->execute($query, $namedParameters);

        return $records;
    }
}