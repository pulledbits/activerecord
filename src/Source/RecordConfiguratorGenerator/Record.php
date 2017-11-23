<?php
namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

use Psr\Http\Message\StreamInterface;
use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\RecordType;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;
use pulledbits\ActiveRecord\Source\TableDescription;
use pulledbits\ActiveRecord\SQL\EntityType;

final class Record implements RecordConfiguratorGenerator
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

    public function generateConfigurator() : RecordConfigurator
    {
        $configurator = new \pulledbits\ActiveRecord\RecordConfigurator($this->recordType);
        $configurator->identifiedBy($this->entityIdentifier);
        $configurator->requires($this->requiredAttributeIdentifiers);
        foreach ($this->references as $referenceIdentifier => $reference) {
            $configurator->references($referenceIdentifier, $reference['table'], $reference['where']);
        }
        return $configurator;
    }
}