<?php

namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\Record;

final class Schema implements \pulledbits\ActiveRecord\Schema
{
    private $recordFactory;

    private $connection;

    private $queryFactory;

    public function __construct(\pulledbits\ActiveRecord\RecordFactory $recordFactory, Connection $connection) {
        $this->recordFactory = $recordFactory;
        $this->queryFactory = new QueryFactory($this->connection);
    }

    private function makeRecord($entityTypeIdentifier, array $values) {
        $record = $this->recordFactory->makeRecord($this, $entityTypeIdentifier);
        $record->contains($values);
        return $record;
    }

    public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions) : array {
        $query = $this->queryFactory->makeSelect($entityTypeIdentifier, $attributeIdentifiers);
        $query->where($this->queryFactory->makeWhere($conditions));
        $result = $query->execute();

        return $result->map(function(array $values) use ($entityTypeIdentifier) {
            return $this->makeRecord($entityTypeIdentifier, $values);
        });
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
        $query = $this->queryFactory->makeUpdate($tableIdentifier, $values);
        $query->where($this->queryFactory->makeWhere($conditions));
        return count($query->execute());
    }

    public function create(string $tableIdentifier, array $values) : int {
        $query = $this->queryFactory->makeInsert($tableIdentifier, $values);
        return count($query->execute());
    }

    public function delete(string $tableIdentifier, array $conditions) : int {
        $query = $this->queryFactory->makeDelete($tableIdentifier);
        $query->where($this->queryFactory->makeWhere($conditions));
        return count($query->execute());
    }

    public function executeProcedure(string $procedureIdentifier, array $arguments): void
    {
        $query = $this->queryFactory->makeProcedure($procedureIdentifier, $arguments);
        $query->execute();
    }
}