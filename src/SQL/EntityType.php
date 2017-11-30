<?php


namespace pulledbits\ActiveRecord\SQL;


use pulledbits\ActiveRecord\Record;
use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\Schema;
use pulledbits\ActiveRecord\SQL\Meta\TableDescription;

class EntityType implements \pulledbits\ActiveRecord\RecordType
{
    private $schema;

    private $entityTypeIdentifier;


    public function __construct(Schema $schema, string $entityTypeIdentifier, TableDescription $entityDescription)
    {
        $this->schema = $schema;
        $this->entityTypeIdentifier = $entityTypeIdentifier;
        $this->entityDescription = $entityDescription;
    }

    public function makeRecord() : Record
    {
        $record = new Entity($this->schema, $this->entityTypeIdentifier);
        $record->identifiedBy($this->entityDescription->identifier);
        if (count($this->entityDescription->requiredAttributeIdentifiers) > 0) {
            $record->requires($this->entityDescription->requiredAttributeIdentifiers);
        }

        if (count($this->entityDescription->references) > 0) {
            foreach ($this->entityDescription->references as $referenceIdentifier => $reference) {
                $record->references($referenceIdentifier,  $reference['table'], $reference['where']);
            }
        }
        return $record;
    }


    public function configure(): Record
    {
        return $this->makeRecord();
    }
}