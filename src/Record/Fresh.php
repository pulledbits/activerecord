<?php

namespace pulledbits\ActiveRecord\Record;


/**
 * Class Fresh
 * @package pulledbits\ActiveRecord\Record
 */
final class Fresh implements \pulledbits\ActiveRecord\Record
{
    /**
     * @var \pulledbits\ActiveRecord\Record
     */
    private $record;

    /**
     * @var bool
     */
    private $created;

    /**
     * Fresh constructor.
     * @param \pulledbits\ActiveRecord\Record $record
     */
    public function __construct(\pulledbits\ActiveRecord\Record $record)
    {
        $this->record = $record;
        $this->created = false;
    }

    /**
     * @param string $property
     */
    public function __get($property)
    {
        return $this->record->__get($property);
    }

    /**
     * @param string $entityTypeIdentifier
     * @param array $conditions
     * @return array
     */
    public function read(string $entityTypeIdentifier, array $conditions): array
    {
        return $this->record->read($entityTypeIdentifier, $conditions);
    }

    /**
     * @param string $entityTypeIdentifier
     * @param array $conditions
     * @return \pulledbits\ActiveRecord\Record
     */
    public function readFirst(string $entityTypeIdentifier, array $conditions): \pulledbits\ActiveRecord\Record
    {
        return $this->record->readFirst($entityTypeIdentifier, $conditions);
    }

    /**
     * @param string $property
     * @param string $value
     */
    public function __set($property, $value)
    {
        if ($this->created === true) {
            $this->record->__set($property, $value);
            return;
        }

        $this->record->contains([$property => $value]);
        if ($this->record->missesRequiredValues()) {
            return;
        }

        if ($this->record->create() === 1) {
            $this->created = true;
        }
    }

    /**
     */
    public function delete() : int
    {
        return $this->record->delete();
    }

    /**
     * @return int
     */
    public function create() : int
    {
        return $this->record->create();
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return $this->record->__call($method, $arguments);
    }

    /**
     * @param string $referenceIdentifier
     * @param string $referencedEntityTypeIdentifier
     * @param array $conditions
     */
    public function references(string $referenceIdentifier, string $referencedEntityTypeIdentifier, array $conditions) {
        return $this->record->references($referenceIdentifier, $referencedEntityTypeIdentifier, $conditions);
    }

    /**
     * @param array $values
     */
    public function contains(array $values)
    {
        return $this->record->contains($values);
    }

    /**
     * @param array $requiredAttributeIdentifiers
     */
    public function requires(array $requiredAttributeIdentifiers)
    {
        return $this->record->requires($requiredAttributeIdentifiers);
    }

    /**
     * @return bool
     */
    public function missesRequiredValues(): bool
    {
        return $this->record->missesRequiredValues();
    }
}