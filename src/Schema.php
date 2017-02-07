<?php
namespace ActiveRecord;

interface Schema {
    public function read(string $tableIdentifier, array $columnIdentifiers, array $whereParameters) : array;
    public function readFirst(string $tableIdentifier, array $columnIdentifiers, array $whereParameters) : Entity;
    public function update(string $tableIdentifier, array $setParameters, array $whereParameters) : int;
    public function create(string $tableIdentifier, array $values) : int;
    public function delete(string $tableIdentifier, array $whereParameters) : int;
}