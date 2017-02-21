<?php

namespace ActiveRecord;


class Entity implements Record
{
    /**
     * @var \ActiveRecord\Schema
     */
    private $schema;

    /**
     * @var string
     */
    private $entityTypeIdentifier;

    /**
     * @var array
     */
    private $primaryKey;

    /**
     * @var array
     */
    private $references;

    /**
     * @var array
     */
    private $values;


    /**
     * @var array
     */
    private $requiredColumnIdentifiers;

    /**
     * Entity constructor.
     * @param Schema $schema
     * @param string $entityTypeIdentifier
     * @param array $primaryKey
     * @param array $references
     * @param array $values
     */
    public function __construct(Schema $schema, string $entityTypeIdentifier, array $primaryKey)
    {
        $this->schema = $schema;
        $this->entityTypeIdentifier = $entityTypeIdentifier;
        $this->primaryKey = $primaryKey;
        $this->references = [];
        $this->values = [];
        $this->requiredColumnIdentifiers = [];
    }

    public function contains(array $values) {
        $this->values += $values;
    }

    private function primaryKey() {
        $sliced = [];
        foreach ($this->values as $key => $value) {
            if (in_array($key, $this->primaryKey, true)) {
                $sliced[$key] = $value;
            }
        }
        return $sliced;
    }

    public function references(string $referenceIdentifier, string $referencedEntityTypeIdentifier, array $conditions) {
        $this->references[$referenceIdentifier] = [
            'table' => $referencedEntityTypeIdentifier,
            'where' => $conditions
        ];
    }

    public function requires(array $attributeIdentifiers) {
        $this->requiredColumnIdentifiers = $attributeIdentifiers;
    }

    /**
     * @param string $property
     */
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
    public function readFirst(string $entityTypeIdentifier, array $conditions) : Record {
        return $this->schema->readFirst($entityTypeIdentifier, [], $this->fillConditions($conditions));
    }

    public function missesRequiredValues(): bool
    {
        return count($this->calculateMissingValues()) > 0;
    }

    private function calculateMissingValues() : array {
        $missing = [];
        foreach ($this->requiredColumnIdentifiers as $requiredColumnIdentifier) {
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

    /**
     * @param string $property
     * @param string $value
     */
    public function __set($property, $value)
    {
        if ($this->missesRequiredValues()) {
            return 0;
        } elseif ($this->schema->update($this->entityTypeIdentifier, [$property => $value], $this->primaryKey()) > 0) {
            $this->values[$property] = $value;
        }
    }

    /**
     */
    public function delete() : int
    {
        return $this->schema->delete($this->entityTypeIdentifier, $this->primaryKey());
    }

    public function create() : int
    {
        $missing = $this->calculateMissingValues();
        if (count($missing) > 0) {
            trigger_error('Required values are missing: ' . join(', ', $missing), E_USER_ERROR);
            return 0;
        }

        return $this->schema->create($this->entityTypeIdentifier, $this->values);
    }

    private function fillConditions(array $conditions) {
        return array_map(function($localColumnIdentifier) { return $this->__get($localColumnIdentifier); }, $conditions);
    }

    private function prepareReference(string $identifier) {
        if (array_key_exists($identifier, $this->references) === false) {
            trigger_error('Reference does not exist `' . $identifier . '`', E_USER_ERROR);
        }
        $reference = $this->references[$identifier];
        $reference['where'] = $this->fillConditions($reference['where']);
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
            return $this->schema->read($reference['table'], [], $this->mergeConditionsWith__callCustomConditions($reference['where'], $arguments));
        } elseif (substr($method, 0, 12) === 'fetchFirstBy') {
            $reference = $this->prepareReference(substr($method, 12));
            return $this->schema->readFirst($reference['table'], [], $this->mergeConditionsWith__callCustomConditions($reference['where'], $arguments));
        }
        return null;
    }
}