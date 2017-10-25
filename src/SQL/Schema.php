<?php

namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\Record;

final class Schema implements \pulledbits\ActiveRecord\Schema
{
    private $recordFactory;

    private $connection;

    public function __construct(\pulledbits\ActiveRecord\RecordFactory $recordFactory, Connection $connection) {
        $this->recordFactory = $recordFactory;
        $this->connection = $connection;
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

    private function prepareParameters(array $parameters) : PreparedParameters {
        $preparedParameters = new PreparedParameters($parameters);
        return $preparedParameters;
    }

    private function executeWhere(string $query, array $whereParameters) : \PDOStatement
    {
        $preparedParameters = $this->prepareParameters($whereParameters);
        $where = new Where($preparedParameters->extractParametersSQL(), $preparedParameters->extractParameters());
        return $this->connection->execute($query . $where, $where->parameters());
    }

    public function update(string $tableIdentifier, array $values, array $conditions) : int {
        $preparedParameters = $this->prepareParameters($values);
        $values = new Update\Values($preparedParameters->extractParametersSQL(), $preparedParameters->extractParameters());
        $query = new Update($tableIdentifier, $values);
        $preparedWhereParameters = $this->prepareParameters($conditions);
        $query->where($preparedWhereParameters->extractParametersSQL(), $preparedWhereParameters->extractParameters());
        return $this->connection->executeChange($query, $query->parameters());
    }

    public function create(string $tableIdentifier, array $values) : int {
        $preparedParameters = $this->prepareParameters($values);
        return $this->connection->executeChange("INSERT INTO " . $tableIdentifier . " (" . join(', ', $preparedParameters->extractColumns()) . ") VALUES (" . join(', ', $preparedParameters->extractParameterizedValues()) . ")", $preparedParameters->extractParameters());
    }

    public function delete(string $tableIdentifier, array $conditions) : int {
        $statement = $this->executeWhere("DELETE FROM " . $tableIdentifier , $conditions);
        return $statement->rowCount();
    }

    public function executeProcedure(string $procedureIdentifier, array $arguments): void
    {
        $preparedParameters = $this->prepareParameters($arguments);
        $this->connection->execute('CALL ' . $procedureIdentifier . '(' . join(", ", array_keys($preparedParameters->extractParameters())) . ')', $preparedParameters->extractParameters());
    }
}