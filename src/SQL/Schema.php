<?php

namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\Record;
use pulledbits\ActiveRecord\SQL\Query\Delete;
use pulledbits\ActiveRecord\SQL\Query\Insert;
use pulledbits\ActiveRecord\SQL\Query\PreparedParameters;
use pulledbits\ActiveRecord\SQL\Query\Procedure;
use pulledbits\ActiveRecord\SQL\Query\Select;
use pulledbits\ActiveRecord\SQL\Query\Update;

final class Schema implements \pulledbits\ActiveRecord\Schema
{
    private $recordFactory;

    private $connection;

    private $queryFactory;

    public function __construct(\pulledbits\ActiveRecord\RecordFactory $recordFactory, Connection $connection) {
        $this->recordFactory = $recordFactory;
        $this->connection = $connection;
        $this->queryFactory = new QueryFactory();
    }

    private function makeRecord($entityTypeIdentifier, array $values) {
        $record = $this->recordFactory->makeRecord($this, $entityTypeIdentifier);
        $record->contains($values);
        return $record;
    }

    public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions) : array {
        $query = $this->queryFactory->makeSelect($entityTypeIdentifier, $attributeIdentifiers);
        $query->where($this->queryFactory->makeWhere($conditions));
        $result = $query->execute($this->connection);

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
        return count($query->execute($this->connection));
    }

    public function create(string $tableIdentifier, array $values) : int {
        $query = $this->queryFactory->makeInsert($tableIdentifier, $values);
        return count($query->execute($this->connection));
    }

    public function delete(string $tableIdentifier, array $conditions) : int {
        $query = $this->queryFactory->makeDelete($tableIdentifier);
        $query->where($this->queryFactory->makeWhere($conditions));
        return count($query->execute($this->connection));
    }

    public function executeProcedure(string $procedureIdentifier, array $arguments): void
    {
        $preparedParameters = new PreparedParameters($arguments);
        $query = $this->queryFactory->makeProcedure($procedureIdentifier, $preparedParameters);
        $query->execute($this->connection);
    }
}