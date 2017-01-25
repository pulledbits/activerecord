<?php
namespace ActiveRecord\Schema;

interface EntityType {

    public function executeEntityConfigurator(string $path, array $values) : \ActiveRecord\Entity;

    public function select(array $columnIdentifiers, array $whereParameters);

    public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters);

    public function insert(array $values);

    public function update(array $setParameters, array $whereParameters);

    public function delete(array $whereParameters);
}