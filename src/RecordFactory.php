<?php
namespace ActiveRecord;

class RecordFactory {
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function makeRecord(Schema $schema, string $entityTypeIdentifier, array $values) : Entity
    {
        $configurator = require $this->path . DIRECTORY_SEPARATOR . $entityTypeIdentifier . '.php';
        return $configurator($schema, $entityTypeIdentifier, $values);
    }
}