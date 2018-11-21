<?php

namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\Entity;
use pulledbits\ActiveRecord\SQL\MySQL\Table;

final class Record implements Entity
{
    private $table;

    private $values;

    private $methods;

    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->values = [];
        $this->methods = [];
    }

    public function contains(array $values)
    {
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
        if ($this->table->update([$property => $value], $this->values) > 0) {
            $this->values[$property] = $value;
        }
    }

    public function delete(): int
    {
        return $this->table->delete($this->table->primaryKey($this->values));
    }

    public function create(): int
    {
        return $this->table->create($this->values);
    }

    public function bind(string $methodIdentifier, callable $callback) : void {
        $this->methods[$methodIdentifier] = \Closure::bind($callback, $this, __CLASS__);;
    }

    public function __call(string $method, array $arguments)
    {
        $conditions = [];
        if (count($arguments) === 1) {
            $conditions = $arguments[0];
        }

        if (substr($method, 0, 7) === 'fetchBy') {
            return $this->table->fetchBy(substr($method, 7), $this->values, $conditions);
        } elseif (substr($method, 0, 11) === 'referenceBy') {
            return $this->table->referenceBy(substr($method, 11), $this->values, $conditions);
        } elseif (array_key_exists($method, $this->methods)) {
            return call_user_func_array($this->methods[$method], $arguments);
        }
        return $this->table->call($method, $arguments);
    }
}