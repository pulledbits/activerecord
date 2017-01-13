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

    const PP_COLUMN = 'column';
    const PP_VALUE = 'value';
    const PP_SQL = 'sql';
    const PP_PARAM = 'parameter';
    const PP_PARAMS = 'parameters';

    private function prepareParameters(array $parameters) {
        $preparedParameters = [];
        foreach ($parameters as $localColumn => $value) {
            $preparedParameters[] = [
                self::PP_COLUMN => $localColumn,
                self::PP_VALUE => $value,
                self::PP_PARAM => ":" . uniqid()
            ];
        }
        return $preparedParameters;
    }

    private function extract(string $type, array $preparedParameters) {
        return array_map(function(array $preparedParameters) use ($type) { return $preparedParameters[$type]; }, $preparedParameters);
    }
    private function extractParameters(array $preparedParameters) {
        return array_combine($this->extract(self::PP_PARAM, $preparedParameters), $this->extract(self::PP_VALUE, $preparedParameters));
    }
    private function extractParametersSQL(array $preparedParameters) {
        return array_map(function($preparedParameter) { return $preparedParameter[self::PP_COLUMN] . " = " . $preparedParameter[self::PP_PARAM]; }, $preparedParameters);
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
        $preparedParameters = $this->prepareParameters($values);
        $this->schema->execute("INSERT INTO " . $this->identifier . " (" . join(', ', $this->extract(self::PP_COLUMN, $preparedParameters)) . ") VALUES (" . join(', ', $this->extract(self::PP_PARAM, $preparedParameters)) . ")", $this->extractParameters($preparedParameters));
        return $this->select(array_keys($values), $values);
    }

    public function update(array $setParameters, array $whereParameters) {
        $preparedParameters = $this->prepareParameters($setParameters);
        $this->schema->executeWhere("UPDATE " . $this->identifier . " SET " . join(", ", $this->extractParametersSQL($preparedParameters)), $whereParameters);
        return $this->select(array_keys($setParameters), $whereParameters);
    }

    public function delete(array $whereParameters) {
        $records = $this->select(array_keys($whereParameters), $whereParameters);
        $this->schema->executeWhere("DELETE FROM " . $this->identifier , $whereParameters);
        return $records;
    }
}