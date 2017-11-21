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

    public function createRecord() : Record
    {
        return new Entity($this->schema, $this->entityTypeIdentifier);
    }

}