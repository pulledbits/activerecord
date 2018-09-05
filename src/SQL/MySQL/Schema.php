<?php

namespace pulledbits\ActiveRecord\SQL\MySQL;

use pulledbits\ActiveRecord\Result;

class Schema implements \pulledbits\ActiveRecord\Schema
{
    private $queryFactory;
    private $identifier;
    private $tables;
    private $tableIdentifiers;

    public function __construct(QueryFactory $queryFactory, string $identifier)
    {
        $this->queryFactory = $queryFactory;
        $this->identifier = $identifier;

        $this->tables = [];

        $this->tableIdentifiers = [];
        foreach ($this->listEntities()->fetchAll() as $baseTable) {
            $tableIdentifier = array_shift($baseTable);
            switch ($baseTable['Table_type']) {
                case 'BASE_TABLE':
                    $this->tableIdentifiers[$tableIdentifier] = 'BASE_TABLE';
                    break;
                case 'VIEW':
                    $this->tableIdentifiers[$tableIdentifier] = 'VIEW';
                    break;
            }
        }
    }

    public function listIndexesForTable(string $tableIdentifier): Result
    {
        $query = $this->queryFactory->makeRaw('SHOW INDEX FROM ' . $this->qualifyEntityTypeIdentifier($tableIdentifier));
        return $query->execute();
    }

    private function qualifyEntityTypeIdentifier(string $entityTypeIdentifier): string
    {
        return $this->identifier . '.' . $entityTypeIdentifier;
    }

    public function listColumnsForTable(string $tableIdentifier): Result
    {
        $query = $this->queryFactory->makeRaw('SHOW FULL COLUMNS IN ' . $this->qualifyEntityTypeIdentifier($tableIdentifier));
        return $query->execute();
    }

    public function listForeignKeys(string $tableIdentifier): Result
    {
        $query = $this->queryFactory->makeRaw('(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'' . $tableIdentifier . '\' */ WHERE k.table_name = \'' . $tableIdentifier . '\' AND k.table_schema = \'' . $this->identifier . '\' /**!50116 AND c.constraint_schema = \'' . $this->identifier . '\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)' . ' UNION ALL ' . '(SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'' . $tableIdentifier . '\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'' . $tableIdentifier . '\' AND k.table_schema = \'' . $this->identifier . '\' /**!50116 AND c.constraint_schema = \'' . $this->identifier . '\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)');
        return $query->execute();
    }

    public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions): array
    {
        $query = $this->queryFactory->makeSelect($this->qualifyEntityTypeIdentifier($entityTypeIdentifier), $attributeIdentifiers);
        $query->where($conditions);
        $result = $query->execute();

        $records = [];
        $recordType = $this->makeEntityType($entityTypeIdentifier);
        foreach ($result->fetchAll() as $row) {
            $records[] = $recordType->makeEntity($row);
        }
        return $records;
    }

    private function makeEntityType(string $recordTypeIdentifier): \pulledbits\ActiveRecord\EntityType
    {
        if (array_key_exists($recordTypeIdentifier, $this->tableIdentifiers) === false) {
            return new Table($this, $recordTypeIdentifier);
        } elseif (array_key_exists($recordTypeIdentifier, $this->tables) === false) {
            $this->tables[$recordTypeIdentifier] = new Table($this, $recordTypeIdentifier);
        }

        if ($this->tableIdentifiers[$recordTypeIdentifier] === 'VIEW') {
            $underscorePosition = strpos($recordTypeIdentifier, '_');
            if ($underscorePosition > 0) {
                $possibleEntityTypeIdentifier = substr($recordTypeIdentifier, 0, $underscorePosition);
                $this->tables[$recordTypeIdentifier] = $this->makeEntityType($possibleEntityTypeIdentifier);
            }
        }

        return $this->tables[$recordTypeIdentifier];
    }


    public function update(string $entityTypeIdentifier, array $values, array $conditions): int
    {
        $query = $this->queryFactory->makeUpdate($this->qualifyEntityTypeIdentifier($entityTypeIdentifier), $values);
        $query->where($conditions);
        return count($query->execute());
    }

    public function create(string $entityTypeIdentifier, array $values): int
    {
        $query = $this->queryFactory->makeInsert($this->qualifyEntityTypeIdentifier($entityTypeIdentifier), $values);
        return count($query->execute());
    }

    public function delete(string $entityTypeIdentifier, array $conditions): int
    {
        $query = $this->queryFactory->makeDelete($this->qualifyEntityTypeIdentifier($entityTypeIdentifier));
        $query->where($conditions);
        return count($query->execute());
    }

    public function executeProcedure(string $procedureIdentifier, array $arguments): void
    {
        $query = $this->queryFactory->makeProcedure($this->qualifyEntityTypeIdentifier($procedureIdentifier), $arguments);
        $query->execute();
    }

    public function listEntities() : Result
    {
        $query = $this->queryFactory->makeRaw('SHOW FULL TABLES IN ' . $this->identifier);
        return $query->execute();
    }
}