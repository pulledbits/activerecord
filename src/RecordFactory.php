<?php
namespace pulledbits\ActiveRecord;

final class RecordFactory {

    private $configurator;

    public function __construct(Record\Configurator $configurator)
    {
        $this->configurator = $configurator;
    }
    public function makeRecord(Schema $schema, string $entityTypeIdentifier) : Entity
    {
        $configurator = $this->configurator->generate($entityTypeIdentifier);
        return $configurator($schema, $entityTypeIdentifier);
    }
}