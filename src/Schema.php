<?php
namespace ActiveRecord;

interface Schema {
    public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters, \Closure $recordConverter) : array;
    public function updateWhere(string $tableIdentifier, array $setParameters, array $whereParameters) : int;
    public function insertValues(string $tableIdentifier, array $values) : int;
    public function deleteFrom(string $tableIdentifier, array $whereParameters) : int;
}