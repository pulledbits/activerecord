<?php
namespace pulledbits\ActiveRecord;

final class RecordFactory {

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function makeRecord(Schema $schema, string $entityTypeIdentifier) : Entity
    {
        $configurator = require $this->path . DIRECTORY_SEPARATOR . $entityTypeIdentifier . '.php';
        return $configurator($schema, $entityTypeIdentifier);
    }
}