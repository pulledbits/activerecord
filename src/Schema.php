<?php
namespace ActiveRecord;

interface Schema {
    public function readFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters) : array;
    public function update(string $tableIdentifier, array $setParameters, array $whereParameters) : int;
    public function create(string $tableIdentifier, array $values) : int;
    public function deleteFrom(string $tableIdentifier, array $whereParameters) : int;
}