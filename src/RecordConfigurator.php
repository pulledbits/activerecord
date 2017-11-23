<?php


namespace pulledbits\ActiveRecord;


class RecordConfigurator
{
    private $recordFactory;
    private $entityIdentifier = [];
    private $requiredAttributeIdentifiers = [];
    private $references = [];

    public function __construct(RecordFactory $recordFactory) {
        $this->recordFactory = $recordFactory;
    }
    public function identifiedBy(array $identifier) {
        $this->entityIdentifier = $identifier;
    }
    public function requires(array $requiredAttributeIdentifiers) {
        $this->requiredAttributeIdentifiers = $requiredAttributeIdentifiers;
    }
    public function references(string $referenceIdentifier, string $referencedEntityTypeIdentifier, array $where) {
        $this->references[$referenceIdentifier] = [$referencedEntityTypeIdentifier, $where];
    }
    public function configure() : Record {
        $record = $this->recordFactory->makeRecord();
        $record->identifiedBy($this->entityIdentifier);
        if (count($this->requiredAttributeIdentifiers) > 0) {
            $record->requires($this->requiredAttributeIdentifiers);
        }

        if (count($this->references) > 0) {
            foreach ($this->references as $referenceIdentifier => $reference) {
                $record->references($referenceIdentifier,  $reference[0], $reference[1]);
            }
        }
        return $record;

    }
}