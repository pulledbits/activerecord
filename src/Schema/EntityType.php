<?php
namespace ActiveRecord\Schema;

interface EntityType {

    public function executeEntityConfigurator(string $path, array $values) : \ActiveRecord\Entity;

    public function select(array $columnIdentifiers, array $whereParameters) : array;

    public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters) : array;

    public function insert(array $values) : int;

    public function update(array $setParameters, array $whereParameters) : int;

    public function delete(array $whereParameters) : int;
}