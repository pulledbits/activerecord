<?php


namespace pulledbits\ActiveRecord\SQL;


use pulledbits\ActiveRecord\Record;
use pulledbits\ActiveRecord\Schema;

class EntityType implements \pulledbits\ActiveRecord\RecordType
{
    private $schema;

    private $entityTypeIdentifier;


    public function __construct(Schema $schema, string $entityTypeIdentifier)
    {
        $this->schema = $schema;
        $this->entityTypeIdentifier = $entityTypeIdentifier;
    }

    public function makeRecord() : Record
    {
        return new Entity($this->schema, $this->entityTypeIdentifier);
    }

}