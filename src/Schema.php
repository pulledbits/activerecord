<?php
namespace pulledbits\ActiveRecord;

interface Schema {

    public function initializeRecord(string $entityTypeIdentifier, array $values) : Record;

    public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions) : array;

    public function readFirst(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions) : Record;

    public function update(string $entityTypeIdentifier, array $values, array $conditions) : int;

    public function create(string $entityTypeIdentifier, array $values) : int;

    public function delete(string $entityTypeIdentifier, array $conditions) : int;

    public function executeProcedure(string $procedureIdentifier, array $arguments) : void;
}