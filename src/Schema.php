<?php
namespace pulledbits\ActiveRecord;

use pulledbits\ActiveRecord\SQL\Meta\TableDescription;

interface Schema {

    public function makeRecord(string $entityTypeIdentifier, TableDescription $entityDescription) : Record;

    public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions) : array;

    public function update(string $entityTypeIdentifier, array $values, array $conditions) : int;

    public function create(string $entityTypeIdentifier, array $values) : int;

    public function delete(string $entityTypeIdentifier, array $conditions) : int;

    public function executeProcedure(string $procedureIdentifier, array $arguments) : void;

    public function listTables() : Result;

    public function listForeignKeys(string $tableIdentifier) : Result;

    public function listViews() : Result;
}