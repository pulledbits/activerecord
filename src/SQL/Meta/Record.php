<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\RecordType;

final class Record implements RecordConfigurator
{
    private $recordType;

    private $entityDescription;

    public function __construct(RecordType $recordType, TableDescription $entityDescription)
    {
        $this->recordType = $recordType;
        $this->entityDescription = $entityDescription;
    }

    public function configure(): \pulledbits\ActiveRecord\Record
    {
        $record = $this->recordType->makeRecord();
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
}