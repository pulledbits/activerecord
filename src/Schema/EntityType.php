<?php
namespace ActiveRecord\Schema;

interface EntityType {

    public function executeRecordClassConfigurator(string $path, array $values) : \ActiveRecord\Record;

    public function select(array $columnIdentifiers, array $whereParameters);

    public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters);

    public function insert(array $values);

    public function update(array $setParameters, array $whereParameters);

    public function delete(array $whereParameters);
}