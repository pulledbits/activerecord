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
     * @param Schema\EntityType $asset
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

    /**
     * @param string $property
     * @param string $value
     */
    public function __set($property, $value)
    {
        if ($this->schema->updateWhere($this->entityTypeIdentifier, [$property => $value], $this->primaryKey) > 0) {
            $this->values[$property] = $value;
        }
    }

    /**
     */
    public function delete()
    {
        return $this->schema->deleteFrom($this->entityTypeIdentifier, $this->primaryKey);
    }

    public function __call(string $method, array $arguments)
    {
        if (substr($method, 0, 7) === 'fetchBy') {
            $reference = $this->references[substr($method, 7)];
            $fkColumns = array_keys($reference['where']);
            $fkLocalColumns = array_values($reference['where']);
            return $this->schema->readFrom($reference['table'], $fkColumns, array_combine($fkColumns, array_slice_key($this->values, $fkLocalColumns)));
        }
    }

}