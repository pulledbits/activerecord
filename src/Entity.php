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
        return $this->values[$property];
    }

    public function read(string $entityTypeIdentifier, array $conditions) : array {
        return $this->schema->read($entityTypeIdentifier, [], array_map(function($localColumnIdentifier) { return $this->__get($localColumnIdentifier); }, $conditions));
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

    public function __call(string $method, array $arguments)
    {
        if (substr($method, 0, 7) === 'fetchBy') {
            $reference = $this->references[substr($method, 7)];
            return $this->fetchBy($reference['table'], $reference['where']);
        } elseif (substr($method, 0, 12) === 'fetchFirstBy') {
            $reference = $this->references[substr($method, 12)];
            return $this->fetchFirstBy($reference['table'], $reference['where']);
        }
        return null;
    }

    private function fetchBy(string $entityTypeIdentifier, array $conditions) : array {
        $fkColumns = array_keys($conditions);
        $fkLocalColumns = array_values($conditions);
        $sliced = [];
        foreach ($this->values as $key => $value) {
            if (in_array($key, $fkLocalColumns, true)) {
                $sliced[$key] = $value;
            }
        }
        return $this->schema->read($entityTypeIdentifier, [], array_combine($fkColumns, $sliced));
    }

    private function fetchFirstBy(string $entityTypeIdentifier, array $conditions) : Entity {
        return $this->fetchBy($entityTypeIdentifier, $conditions)[0];
    }

}