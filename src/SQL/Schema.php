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
        $query = new Select($entityTypeIdentifier, $attributeIdentifiers);
        $query->where(new PreparedParameters($conditions));
        $statement = $query->execute($this->connection);

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
        $values = new Update\Values(new PreparedParameters($values));
        $query = new Update($tableIdentifier, $values);
        $query->where(new PreparedParameters($conditions));
        return $query->execute($this->connection);
    }

    public function create(string $tableIdentifier, array $values) : int {
        $preparedParameters = new PreparedParameters($values);
        $query = new Insert($tableIdentifier, $preparedParameters);
        return $query->execute($this->connection);
    }

    public function delete(string $tableIdentifier, array $conditions) : int {
        $query = new Delete($tableIdentifier);
        $query->where(new PreparedParameters($conditions));
        return $query->execute($this->connection);
    }

    public function executeProcedure(string $procedureIdentifier, array $arguments): void
    {
        $preparedParameters = new PreparedParameters($arguments);
        $query = new Procedure($procedureIdentifier, $preparedParameters);
        $query->execute($this->connection);
    }
}