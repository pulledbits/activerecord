<?php

namespace ActiveRecord;


class Entity
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
    private $primaryKey = NULL;

    /**
     * @var array
     */
    private $references = NULL;

    /**
     * @var array
     */
    private $values = NULL;

    /**
     * Entity constructor.
     * @param Schema $schema
     * @param string $entityTypeIdentifier
     * @param array $primaryKey
     * @param array $references
     * @param array $values
     */
    public function __construct(Schema $schema, string $entityTypeIdentifier, array $primaryKey, array $references, array $values)
    {
        $this->schema = $schema;
        $this->entityTypeIdentifier = $entityTypeIdentifier;
        $this->primaryKey = $primaryKey;
        $this->references = $references;
        $this->values = $values;
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
    public function readFirst(string $entityTypeIdentifier, array $conditions) : Entity {
        return $this->schema->readFirst($entityTypeIdentifier, [], $this->fillConditions($conditions));
    }

    /**
     * @param string $property
     * @param string $value
     */
    public function __set($property, $value)
    {
        if ($this->schema->update($this->entityTypeIdentifier, [$property => $value], $this->primaryKey) > 0) {
            $this->values[$property] = $value;
        }
    }

    /**
     */
    public function delete()
    {
        return $this->schema->delete($this->entityTypeIdentifier, $this->primaryKey);
    }

    public function create()
    {
        return $this->schema->create($this->entityTypeIdentifier, $this->values);
    }

    private function fillConditions(array $conditions) {
        return array_map(function($localColumnIdentifier) { return $this->__get($localColumnIdentifier); }, $conditions);
    }

    private function prepareReference(string $identifier) {
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