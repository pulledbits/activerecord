<?php

namespace pulledbits\ActiveRecord;

interface Schema
{

    public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions): array;

    public function update(string $entityTypeIdentifier, array $values, array $conditions): int;

    public function create(string $entityTypeIdentifier, array $values): int;

    public function delete(string $entityTypeIdentifier, array $conditions): int;

    public function executeProcedure(string $procedureIdentifier, array $arguments): void;

    public function listEntities() : Result;

    public function listForeignKeys(string $tableIdentifier): Result;

    public function listIndexesForTable(string $tableIdentifier): Result;

    public function listColumnsForTable(string $tableIdentifier): Result;
}