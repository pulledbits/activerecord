<?php
namespace pulledbits\ActiveRecord\Source\RecordConfigurator;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\RecordType;
use pulledbits\ActiveRecord\Source\TableDescription;

final class Record implements RecordConfigurator
{
    const NEWLINE = PHP_EOL;

    private $entityIdentifier;

    private $requiredAttributeIdentifiers;

    private $references;

    public function __construct(RecordType $recordType, TableDescription $entityDescription)
    {
        $this->recordType = $recordType;
        $this->entityIdentifier = $entityDescription->identifier;
        $this->requiredAttributeIdentifiers = $entityDescription->requiredAttributeIdentifiers;
        $this->references = [];
        foreach ($entityDescription->references as $referenceIdentifier => $reference) {
            $this->references[$referenceIdentifier] = [
                'table' => $reference['table'],
                'where' => $reference['where']
            ];
        }
    }

    public function configure(): \pulledbits\ActiveRecord\Record
    {
        $record = $this->recordType->makeRecord();
        $record->identifiedBy($this->entityIdentifier);
        if (count($this->requiredAttributeIdentifiers) > 0) {
            $record->requires($this->requiredAttributeIdentifiers);
        }

        if (count($this->references) > 0) {
            foreach ($this->references as $referenceIdentifier => $reference) {
                $record->references($referenceIdentifier,  $reference['table'], $reference['where']);
            }
        }
        return $record;
    }
}