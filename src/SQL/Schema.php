<?php

namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\Record;

final class Schema implements \pulledbits\ActiveRecord\Schema
{
    private $recordFactory;

    private $connection;

    public function __construct(\pulledbits\ActiveRecord\RecordFactory $recordFactory, PDO $connection) {
        $this->recordFactory = $recordFactory;
        $this->connection = $connection;
    }

    private function executeWhere(string $query, array $whereParameters) : \PDOStatement
    {
        $where = $this->makeWhereCondition($whereParameters);
        return $this->connection->execute($query . $where[self::PP_SQL], $where[self::PP_PARAMS]);
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

    private function makeWhereCondition(array $whereParameters) {
        $preparedParameters = $this->prepareParameters($whereParameters);
        if (count($preparedParameters) === 0) {
            return [self::PP_SQL => '', self::PP_PARAMS => []];
        }
        return [
            self::PP_SQL => " WHERE " . join(" AND ", $this->extractParametersSQL($preparedParameters)),
            self::PP_PARAMS => $this->extractParameters($preparedParameters)
        ];
    }

    private function makeRecord($entityTypeIdentifier, array $values) {
        $record = $this->recordFactory->makeRecord($this, $entityTypeIdentifier);
        $record->contains($values);
        return $record;
    }

    public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions) : array {
        if (count($attributeIdentifiers) === 0) {
            $attributeIdentifiers[] = '*';
        }
        $statement = $this->executeWhere("SELECT " . join(', ', $attributeIdentifiers) . " FROM " . $entityTypeIdentifier, $conditions);

        return array_map(function(array $values) use ($entityTypeIdentifier) {
            return $this->makeRecord($entityTypeIdentifier, $values);
        }, $statement->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function initializeRecord(string $entityTypeIdentifier, array $values) : Record {
        $record = $this->makeRecord($entityTypeIdentifier, $values);
        return new Record\Fresh($record);
    }

    public function readFirst(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions) : \pulledbits\ActiveRecord\Record {
        $records = $this->read($entityTypeIdentifier, $attributeIdentifiers, $conditions);
        if (count($records) === 0) {
            return $this->initializeRecord($entityTypeIdentifier, $conditions);
        }
        return $records[0];
    }

    public function update(string $tableIdentifier, array $values, array $conditions) : int {
        $preparedParameters = $this->prepareParameters($values);
        $query = "UPDATE " . $tableIdentifier . " SET " . join(", ", $this->extractParametersSQL($preparedParameters));
        $where = $this->makeWhereCondition($conditions);
        $statement = $this->connection->execute($query . $where[self::PP_SQL], array_merge($this->extractParameters($preparedParameters), $where[self::PP_PARAMS]));
        return $statement->rowCount();
    }

    public function create(string $tableIdentifier, array $values) : int {
        $preparedParameters = $this->prepareParameters($values);
        $statement = $this->connection->execute("INSERT INTO " . $tableIdentifier . " (" . join(', ', $this->extract(Schema::PP_COLUMN, $preparedParameters)) . ") VALUES (" . join(', ', $this->extract(Schema::PP_PARAM, $preparedParameters)) . ")", $this->extractParameters($preparedParameters));
        return $statement->rowCount();
    }

    public function delete(string $tableIdentifier, array $conditions) : int {
        $statement = $this->executeWhere("DELETE FROM " . $tableIdentifier , $conditions);
        return $statement->rowCount();
    }

    public function executeProcedure(string $procedureIdentifier, array $arguments): void
    {
        $preparedParameters = $this->prepareParameters($arguments);
        $this->connection->execute('CALL ' . $procedureIdentifier . '(' . join(", ", array_keys($this->extractParameters($preparedParameters))) . ')', $this->extractParameters($preparedParameters));
    }
}