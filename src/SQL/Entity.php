<?php

namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\Record;
use pulledbits\ActiveRecord\Schema;

final class Entity implements Record
{
    private $entityType;

    private $values;

    public function __construct(EntityType $entityType)
    {
        $this->entityType = $entityType;
        $this->values = [];
    }

    public function contains(array $values) {
        $this->values += $values;
    }

    public function __get($property)
    {
        if (array_key_exists($property, $this->values) === false) {
            return null;
        }
        return $this->values[$property];
    }

    public function __set($property, $value)
    {
        if (count($this->entityType->calculateMissingValues($this->values)) > 0) {
            return 0;
        } elseif ($this->entityType->update([$property => $value], $this->entityType->primaryKey($this->values)) > 0) {
            $this->values[$property] = $value;
        }
    }

    public function delete() : int
    {
        return $this->entityType->delete($this->entityType->primaryKey($this->values));
    }

    public function create() : int
    {
        return $this->entityType->create($this->values);
    }

    public function __call(string $method, array $arguments)
    {
        $conditions = [];
        if (count($arguments) === 1) {
            $conditions = $arguments[0];
        }

        if (substr($method, 0, 7) === 'fetchBy') {
            return $this->entityType->fetchBy(substr($method, 7), $this->values, $conditions);
        } elseif (substr($method, 0, 11) === 'referenceBy') {
            return $this->entityType->referenceBy(substr($method, 11), $this->values, $conditions);
        }
        return null;
    }
}