<?php

namespace pulledbits\ActiveRecord\SQL\MySQL;

use pulledbits\ActiveRecord\Result;
use pulledbits\ActiveRecord\SQL\Connection;

class Schema implements \pulledbits\ActiveRecord\Schema
{

    private $connection;
    private $queryFactory;
    private $identifier;
    private $entityTypes;

    public function __construct(Connection $connection, QueryFactory $queryFactory, string $identifier)
    {
        $this->connection = $connection;
        $this->queryFactory = $queryFactory;
        $this->identifier = $identifier;
        $this->entityTypes = new EntityTypes($this);
    }

    public function listIndexesForTable(string $tableIdentifier): Result
    {
        return $this->connection->execute('SHOW INDEX FROM ' . $this->qualifyEntityTypeIdentifier($tableIdentifier), []);
    }

    private function qualifyEntityTypeIdentifier(string $entityTypeIdentifier): string
    {
        return $this->identifier . '.' . $entityTypeIdentifier;
    }

    public function listColumnsForTable(string $tableIdentifier): Result
    {
        return $this->connection->execute('SHOW FULL COLUMNS IN ' . $this->qualifyEntityTypeIdentifier($tableIdentifier), []);
    }

    public function listForeignKeys(string $tableIdentifier): Result
    {
        return $this->connection->execute('(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'' . $tableIdentifier . '\' */ WHERE k.table_name = \'' . $tableIdentifier . '\' AND k.table_schema = \'' . $this->identifier . '\' /**!50116 AND c.constraint_schema = \'' . $this->identifier . '\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)' . ' UNION ALL ' . '(SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'' . $tableIdentifier . '\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'' . $tableIdentifier . '\' AND k.table_schema = \'' . $this->identifier . '\' /**!50116 AND c.constraint_schema = \'' . $this->identifier . '\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)', []);
    }

    public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions): array
    {
        $query = $this->queryFactory->makeSelect($this->qualifyEntityTypeIdentifier($entityTypeIdentifier), $attributeIdentifiers);
        $query->where($conditions);
        $result = $query->execute($this->connection);

        $records = [];
        $recordType = $this->entityTypes->makeRecordType($entityTypeIdentifier);
        foreach ($result->fetchAll() as $row) {
            $record = $recordType->makeRecord();
            $record->contains($row);
            $records[] = $record;
        }
        return $records;
    }

    public function update(string $entityTypeIdentifier, array $values, array $conditions): int
    {
        $query = $this->queryFactory->makeUpdate($this->qualifyEntityTypeIdentifier($entityTypeIdentifier), $values);
        $query->where($conditions);
        return count($query->execute($this->connection));
    }

    public function create(string $entityTypeIdentifier, array $values): int
    {
        $query = $this->queryFactory->makeInsert($this->qualifyEntityTypeIdentifier($entityTypeIdentifier), $values);
        return count($query->execute($this->connection));
    }

    public function delete(string $entityTypeIdentifier, array $conditions): int
    {
        $query = $this->queryFactory->makeDelete($this->qualifyEntityTypeIdentifier($entityTypeIdentifier));
        $query->where($conditions);
        return count($query->execute($this->connection));
    }

    public function executeProcedure(string $procedureIdentifier, array $arguments): void
    {
        $query = $this->queryFactory->makeProcedure($this->qualifyEntityTypeIdentifier($procedureIdentifier), $arguments);
        $query->execute($this->connection);
    }

    public function listEntities() : Result
    {
        return $this->connection->execute('SHOW FULL TABLES IN ' . $this->identifier, []);
    }
}