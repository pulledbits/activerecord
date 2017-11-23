<?php
namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

use Psr\Http\Message\StreamInterface;
use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\RecordFactory;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;
use pulledbits\ActiveRecord\Source\TableDescription;

final class Record implements RecordConfiguratorGenerator
{
    const NEWLINE = PHP_EOL;

    private $entityIdentifier;

    private $requiredAttributeIdentifiers;

    private $references;

    public function __construct(TableDescription $entityDescription)
    {
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

    public function generateConfigurator(RecordFactory $recordFactory) : RecordConfigurator
    {
        $configurator = new \pulledbits\ActiveRecord\RecordConfigurator($recordFactory);
        $configurator->identifiedBy($this->entityIdentifier);
        $configurator->requires($this->requiredAttributeIdentifiers);
        foreach ($this->references as $referenceIdentifier => $reference) {
            $configurator->references($referenceIdentifier, $reference['table'], $reference['where']);
        }
        return $configurator;
    }
}