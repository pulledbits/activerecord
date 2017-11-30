<?php

namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\Record;
use pulledbits\ActiveRecord\Schema;
use pulledbits\ActiveRecord\SQL\Meta\TableDescription;

final class Entity implements Record
{
    private $schema;

    private $entityTypeIdentifier;

    private $entityDescription;

    private $references;

    private $values;

    private $requiredAttributeIdentifiers;

    public function __construct(Schema $schema, string $entityTypeIdentifier, TableDescription $entityDescription)
    {
        $this->schema = $schema;
        $this->entityTypeIdentifier = $entityTypeIdentifier;
        $this->entityDescription = $entityDescription;
        $this->values = [];

        $this->requiredAttributeIdentifiers = $entityDescription->requiredAttributeIdentifiers;

        if (count($entityDescription->references) > 0) {
            foreach ($entityDescription->references as $referenceIdentifier => $reference) {
                $this->references($referenceIdentifier,  $reference['table'], $reference['where']);
            }
        }

    }

    public function contains(array $values) {
        $this->values += $values;
    }

    public function references(string $referenceIdentifier, string $referencedEntityTypeIdentifier, array $conditions) {
        $this->references[$referenceIdentifier] = [
            'entityTypeIdentifier' => $referencedEntityTypeIdentifier,
            'conditions' => $conditions
        ];
    }

    public function requires(array $attributeIdentifiers) {
        $this->requiredAttributeIdentifiers = $attributeIdentifiers;
    }

    public function __get($property)
    {
        if (array_key_exists($property, $this->values) === false) {
            return null;
        }
        return $this->values[$property];
    }

    public function read(string $entityTypeIdentifier, array $conditions) : array {
        return $this->schema->read($entityTypeIdentifier, [], $this->fillConditions($conditions));
    }

    public function missesRequiredValues(): bool
    {
        return count($this->calculateMissingValues()) > 0;
    }

    private function calculateMissingValues() : array {
        $missing = [];
        foreach ($this->requiredAttributeIdentifiers as $requiredColumnIdentifier) {
            if (array_key_exists($requiredColumnIdentifier, $this->values) === false) {
                $missing[] = $requiredColumnIdentifier;
                break;
            } elseif ($this->values[$requiredColumnIdentifier] === null) {
                $missing[] = $requiredColumnIdentifier;
                break;
            }
        }
        return $missing;
    }

    public function __set($property, $value)
    {
        if ($this->missesRequiredValues()) {
            return 0;
        } elseif ($this->schema->update($this->entityTypeIdentifier, [$property => $value], $this->entityDescription->primaryKey($this->values)) > 0) {
            $this->values[$property] = $value;
        }
    }

    public function delete() : int
    {
        return $this->schema->delete($this->entityTypeIdentifier, $this->entityDescription->primaryKey($this->values));
    }

    public function create() : int
    {
        $missing = $this->calculateMissingValues();
        if (count($missing) === 0) {
            return $this->schema->create($this->entityTypeIdentifier, $this->values);
        }

        trigger_error('Required values are missing: ' . join(', ', $missing), E_USER_ERROR);
    }

    private function fillConditions(array $conditions) {
        return array_map(function($localColumnIdentifier) { return $this->__get($localColumnIdentifier); }, $conditions);
    }

    private function prepareReference(string $identifier) {
        if (array_key_exists($identifier, $this->references) === false) {
            trigger_error('Reference does not exist `' . $identifier . '`', E_USER_ERROR);
        }
        $reference = $this->references[$identifier];
        $reference['conditions'] = $this->fillConditions($reference['conditions']);
        return $reference;
    }

    private function mergeConditionsWith__callCustomConditions(array $conditions, array $arguments) {
        if (count($arguments) === 1) {
            return array_merge($arguments[0], $conditions);
        }
        return $conditions;
    }

    public function __call(string $method, array $arguments)
    {
        if (substr($method, 0, 7) === 'fetchBy') {
            $reference = $this->prepareReference(substr($method, 7));
            return $this->schema->read($reference['entityTypeIdentifier'], [], $this->mergeConditionsWith__callCustomConditions($reference['conditions'], $arguments));
        } elseif (substr($method, 0, 11) === 'referenceBy') {
            $reference = $this->prepareReference(substr($method, 11));
            $this->schema->create($reference['entityTypeIdentifier'], $this->mergeConditionsWith__callCustomConditions($reference['conditions'], $arguments));
            $records = $this->schema->read($reference['entityTypeIdentifier'], [], $this->mergeConditionsWith__callCustomConditions($reference['conditions'], $arguments));
            return $records[0];
        }
        return null;
    }
}