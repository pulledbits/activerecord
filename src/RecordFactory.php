<?php
namespace pulledbits\ActiveRecord;

final class RecordFactory {

    private $configurator;

    public function __construct(\pulledbits\ActiveRecord\Configurator $configurator)
    {
        $this->configurator = $configurator;
    }
    public function makeRecord(Schema $schema, string $entityTypeIdentifier) : Entity
    {
        $record = new Entity($schema, $entityTypeIdentifier);
        $configurator = $record->generateConfigurator($this->configurator);
        return $configurator($record);
    }
}