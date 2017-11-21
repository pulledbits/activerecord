<?php

namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\RecordFactory;

final class Schema implements \pulledbits\ActiveRecord\Schema
{

    private $queryFactory;

    public function __construct(QueryFactory $queryFactory) {
        $this->queryFactory = $queryFactory;
    }

    public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions) : array {
        $query = $this->queryFactory->makeSelect($entityTypeIdentifier, $attributeIdentifiers);
        $query->where($this->queryFactory->makeWhere($conditions));
        $result = $query->execute();
        return $result->fetchAllAs();
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