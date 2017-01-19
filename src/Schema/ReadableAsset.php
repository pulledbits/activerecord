<?php
namespace ActiveRecord\Schema;

interface ReadableAsset {
    public function select(array $columnIdentifiers, array $whereParameters);
    public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters);
}