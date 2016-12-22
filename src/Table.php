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

    public function select(string $tableIdentifer, array $columnIdentifiers, array $whereParameters)
    {
        $namedParameters = [];
        $query = "SELECT " . join(', ', $this->schema->prepareFields($columnIdentifiers)) . " FROM " . $tableIdentifer . $this->schema->makeWhereCondition($whereParameters, $namedParameters);
        $statement = $this->schema->execute($query, $namedParameters);

        $recordClassIdentifier = $this->schema->transformTableIdentifierToRecordClassIdentifier($tableIdentifer);
        $table = $this;
        return array_map(function(array $values) use ($recordClassIdentifier, $table) {
            return new $recordClassIdentifier($table, $values);
        }, $statement->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function insert(string $tableIdentifer, array $values) {
        list($insertValues, $insertNamedParameters) = $this->schema->prepareParameters('values', $values);
        $query = "INSERT INTO " . $tableIdentifer . " (" . join(', ', array_keys($insertValues)) . ") VALUES (" . join(', ', array_keys($insertNamedParameters)) . ")";
        $statement = $this->schema->execute($query, $insertNamedParameters);
        return $this->select($tableIdentifer, array_keys($values), $values);
    }

    public function update(string $tableIdentifer, array $setParameters, array $whereParameters) {
        list($set, $namedParameters) = $this->schema->prepareParameters('set', $setParameters);

        $query = "UPDATE " . $tableIdentifer . " SET " . join(", ", $set) . $this->schema->makeWhereCondition($whereParameters, $namedParameters);

        $this->schema->execute($query, $namedParameters);

        return $this->select($tableIdentifer, array_keys($setParameters), $whereParameters);
    }

    public function delete(string $tableIdentifer, array $whereParameters) {
        $namedParameters = [];
        $query = "DELETE FROM " . $tableIdentifer . $this->schema->makeWhereCondition($whereParameters, $namedParameters);


        $records = $this->select($tableIdentifer, array_keys($whereParameters), $whereParameters);

        $statement = $this->schema->execute($query, $namedParameters);

        return $records;
    }
}