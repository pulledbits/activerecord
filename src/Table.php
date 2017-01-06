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

    public function transformColumnToProperty($columnIdentifier)
    {
        return self::COLUMN_PROPERTY_ESCAPE . $columnIdentifier;
    }

    public function select(array $columnIdentifiers, array $whereParameters)
    {
        $namedParameters = [];
        $query = "SELECT " . join(', ', $columnIdentifiers) . " FROM " . $this->identifier . $this->schema->makeWhereCondition($whereParameters, $namedParameters);
        $statement = $this->schema->execute($query, $namedParameters);

        $recordClassIdentifier = $this->schema->transformTableIdentifierToRecordClassIdentifier($this->identifier);
        $table = $this;
        return array_map(function(array $values) use ($recordClassIdentifier, $table) {
            return new $recordClassIdentifier($table, $values);
        }, $statement->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function insert(array $values) {
        list($insertValues, $insertNamedParameters) = $this->schema->prepareParameters('values', $values);
        $query = "INSERT INTO " . $this->identifier . " (" . join(', ', array_keys($insertValues)) . ") VALUES (" . join(', ', array_keys($insertNamedParameters)) . ")";
        $statement = $this->schema->execute($query, $insertNamedParameters);
        return $this->select(array_keys($values), $values);
    }

    public function update(array $setParameters, array $whereParameters) {
        list($set, $setNamedParameters) = $this->schema->prepareParameters('set', $setParameters);

        $namedParameters = [];
        $query = "UPDATE " . $this->identifier . " SET " . join(", ", $set) . $this->schema->makeWhereCondition($whereParameters, $namedParameters);

        $this->schema->execute($query, array_merge($setNamedParameters, $namedParameters));

        return $this->select(array_keys($setParameters), $whereParameters);
    }

    public function delete(array $whereParameters) {
        $namedParameters = [];
        $query = "DELETE FROM " . $this->identifier . $this->schema->makeWhereCondition($whereParameters, $namedParameters);


        $records = $this->select(array_keys($whereParameters), $whereParameters);

        $statement = $this->schema->execute($query, $namedParameters);

        return $records;
    }
}