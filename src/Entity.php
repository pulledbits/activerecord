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

    private function fillConditions(array $conditions) {
        return array_map(function($localColumnIdentifier) { return $this->__get($localColumnIdentifier); }, $conditions);
    }

    public function __call(string $method, array $arguments)
    {
        if (substr($method, 0, 7) === 'fetchBy') {
            $reference = $this->references[substr($method, 7)];
            $conditions = $this->fillConditions($reference['where']);
            if (count($arguments) === 1) {
                $conditions = array_merge($arguments[0], $conditions);
            }
            return $this->schema->read($reference['table'], [], $conditions);
        } elseif (substr($method, 0, 12) === 'fetchFirstBy') {
            $reference = $this->references[substr($method, 12)];
            $conditions = $this->fillConditions($reference['where']);
            if (count($arguments) === 1) {
                $conditions = array_merge($arguments[0], $conditions);
            }
            return $this->schema->readFirst($reference['table'], [], $conditions);
        }
        return null;
    }
}