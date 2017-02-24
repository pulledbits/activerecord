<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 21-2-17
 * Time: 14:54
 */

namespace pulledbits\ActiveRecord\Record;


final class Fresh implements \pulledbits\ActiveRecord\Record
{
    /**
     * @var \pulledbits\ActiveRecord\Record
     */
    private $record;

    private $created;

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

    public function read(string $entityTypeIdentifier, array $conditions): array
    {
        return $this->record->read($entityTypeIdentifier, $conditions);
    }

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

    public function create() : int
    {
        return $this->record->create();
    }

    public function __call(string $method, array $arguments)
    {
        return $this->record->__call($method, $arguments);
    }

    public function references(string $referenceIdentifier, string $referencedEntityTypeIdentifier, array $conditions) {
        return $this->record->references($referenceIdentifier, $referencedEntityTypeIdentifier, $conditions);
    }

    public function contains(array $values)
    {
        return $this->record->contains($values);
    }

    public function requires(array $requiredAttributeIdentifiers)
    {
        return $this->record->requires($requiredAttributeIdentifiers);
    }

    public function missesRequiredValues(): bool
    {
        return $this->record->missesRequiredValues();
    }
}