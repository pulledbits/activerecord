<?php

namespace pulledbits\ActiveRecord;


/**
 * Class Entity
 * @package pulledbits\ActiveRecord
 */
final class Entity implements Record
{
    /**
     * @var \pulledbits\ActiveRecord\Schema
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
    private $requiredAttributeIdentifiers;

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
        $this->requiredAttributeIdentifiers = [];
    }

    /**
     * @param array $values
     */
    public function contains(array $values) {
        $this->values += $values;
    }

    /**
     * @return array
     */
    private function primaryKey() {
        $sliced = [];
        foreach ($this->values as $key => $value) {
            if (in_array($key, $this->primaryKey, true)) {
                $sliced[$key] = $value;
            }
        }
        return $sliced;
    }

    /**
     * @param string $referenceIdentifier
     * @param string $referencedEntityTypeIdentifier
     * @param array $conditions
     */
    public function references(string $referenceIdentifier, string $referencedEntityTypeIdentifier, array $conditions) {
        $this->references[$referenceIdentifier] = [
            'entityTypeIdentifier' => $referencedEntityTypeIdentifier,
            'conditions' => $conditions
        ];
    }

    /**
     * @param array $attributeIdentifiers
     */
    public function requires(array $attributeIdentifiers) {
        $this->requiredAttributeIdentifiers = $attributeIdentifiers;
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

    /**
     * @param string $entityTypeIdentifier
     * @param array $conditions
     * @return array
     */
    public function read(string $entityTypeIdentifier, array $conditions) : array {
        return $this->schema->read($entityTypeIdentifier, [], $this->fillConditions($conditions));
    }

    /**
     * @param string $entityTypeIdentifier
     * @param array $conditions
     * @return Record
     */
    public function readFirst(string $entityTypeIdentifier, array $conditions) : Record {
        return $this->schema->readFirst($entityTypeIdentifier, [], $this->fillConditions($conditions));
    }

    /**
     * @return bool
     */
    public function missesRequiredValues(): bool
    {
        return count($this->calculateMissingValues()) > 0;
    }

    /**
     * @return array
     */
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

    /**
     * @return int
     */
    public function create() : int
    {
        $missing = $this->calculateMissingValues();
        if (count($missing) > 0) {
            trigger_error('Required values are missing: ' . join(', ', $missing), E_USER_ERROR);
            return 0;
        }

        return $this->schema->create($this->entityTypeIdentifier, $this->values);
    }

    /**
     * @param array $conditions
     * @return array
     */
    private function fillConditions(array $conditions) {
        return array_map(function($localColumnIdentifier) { return $this->__get($localColumnIdentifier); }, $conditions);
    }

    /**
     * @param string $identifier
     * @return mixed
     */
    private function prepareReference(string $identifier) {
        if (array_key_exists($identifier, $this->references) === false) {
            trigger_error('Reference does not exist `' . $identifier . '`', E_USER_ERROR);
        }
        $reference = $this->references[$identifier];
        $reference['conditions'] = $this->fillConditions($reference['conditions']);
        return $reference;
    }

    /**
     * @param array $conditions
     * @param array $arguments
     * @return array
     */
    private function mergeConditionsWith__callCustomConditions(array $conditions, array $arguments) {
        if (count($arguments) === 1) {
            return array_merge($arguments[0], $conditions);
        }
        return $conditions;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return array|null|Record|Record[]
     */
    public function __call(string $method, array $arguments)
    {
        if (substr($method, 0, 7) === 'fetchBy') {
            $reference = $this->prepareReference(substr($method, 7));
            return $this->schema->read($reference['entityTypeIdentifier'], [], $this->mergeConditionsWith__callCustomConditions($reference['conditions'], $arguments));
        } elseif (substr($method, 0, 12) === 'fetchFirstBy') {
            $reference = $this->prepareReference(substr($method, 12));
            return $this->schema->readFirst($reference['entityTypeIdentifier'], [], $this->mergeConditionsWith__callCustomConditions($reference['conditions'], $arguments));
        }
        return null;
    }
}