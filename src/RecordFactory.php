<?php


namespace pulledbits\ActiveRecord;


class RecordFactory
{
    private $schema;

    private $entityTypeIdentifier;


    public function __construct(Schema $schema, string $entityTypeIdentifier)
    {
        $this->schema = $schema;
        $this->entityTypeIdentifier = $entityTypeIdentifier;

    }

    public function createRecord(Configurator $configuratorGenerator) : Record
    {
        $record = new Entity($this->schema, $this->entityTypeIdentifier);
        $record->configure($configuratorGenerator);
        $configurator = $configuratorGenerator->generate($this->entityTypeIdentifier);
        return $configurator($record);
    }

}