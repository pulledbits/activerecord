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
    private $schema;

    public function __construct(Schema $schema) {
        $this->schema = $schema;
    }

    private function makeWhereCondition(array $whereParameters, array &$namedParameters) {
        list($where, $namedParameters) = $this->schema->prepareParameters('where', $whereParameters);
        if (count($where) === 0) {
            return "";
        }
        return " WHERE " . join(" AND ", $where);
    }

    public function select(string $tableIdentifer, array $columnIdentifiers, array $whereParameters)
    {
        $namedParameters = [];
        $query = "SELECT " . join(', ', $this->schema->prepareFields($columnIdentifiers)) . " FROM " . $tableIdentifer . $this->makeWhereCondition($whereParameters, $namedParameters);
        $statement = $this->schema->execute($query, $namedParameters);
        return $statement->fetchAll(\PDO::FETCH_CLASS, $this->schema->transformTableIdentifierToRecordClassIdentifier($tableIdentifer), [$this]);
    }

    public function insert(string $tableIdentifer, array $values) {
        list($insertValues, $insertNamedParameters) = $this->schema->prepareParameters('values', $values);

        $query = "INSERT INTO " . $tableIdentifer . " (" . join(', ', array_keys($insertValues)) . ") VALUES (" . join(', ', array_keys($insertNamedParameters)) . ")";
        $statement = $this->schema->execute($query, $insertNamedParameters);

        $recordClassIdentifier = $this->schema->transformTableIdentifierToRecordClassIdentifier($tableIdentifer);
        return $this->select($tableIdentifer, array_keys($values), $recordClassIdentifier::wherePrimaryKey($values));
    }

    public function update(string $tableIdentifer, array $setParameters, array $whereParameters) {
        list($set, $namedParameters) = $this->schema->prepareParameters('set', $setParameters);

        $query = "UPDATE " . $tableIdentifer . " SET " . join(", ", $set) . $this->makeWhereCondition($whereParameters, $namedParameters);

        $this->schema->execute($query, $namedParameters);

        return $this->select($tableIdentifer, array_keys($setParameters), $whereParameters);
    }

    public function delete(string $tableIdentifer, array $whereParameters) {
        $namedParameters = [];
        $query = "DELETE FROM " . $tableIdentifer . $this->makeWhereCondition($whereParameters, $namedParameters);


        $records = $this->select($tableIdentifer, array_keys($whereParameters), $whereParameters);

        $statement = $this->schema->execute($query, $namedParameters);

        return $records;
    }
}