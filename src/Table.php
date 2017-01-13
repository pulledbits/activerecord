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

    private function fetchRecord($values) {
        $recordClassIdentifier = $this->schema->transformTableIdentifierToRecordClassIdentifier($this->identifier);
        return new $recordClassIdentifier($this, $values);
    }

    public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters)
    {
        $statement = $this->schema->executeWhere("SELECT " . join(', ', $columnIdentifiers) . " FROM " . $tableIdentifier, $whereParameters);
        return array_map(array($this, 'fetchRecord'), $statement->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function insert(array $values) {
        $this->schema->insertValues($this->identifier, $values);
        return $this->select(array_keys($values), $values);
    }

    public function update(array $setParameters, array $whereParameters) {
        $this->schema->updateWhere($this->identifier, $setParameters, $whereParameters);
        return $this->select(array_keys($setParameters), $whereParameters);
    }

    public function delete(array $whereParameters) {
        $records = $this->select(array_keys($whereParameters), $whereParameters);
        $this->schema->executeWhere("DELETE FROM " . $this->identifier , $whereParameters);
        return $records;
    }
}