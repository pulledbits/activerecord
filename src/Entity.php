<?php

namespace pulledbits\ActiveRecord;

interface Entity
{
    public function contains(array $values);

    public function __get($property);

    public function __set($property, $value);

    public function delete(): int;

    public function create(): int;

    public function __call(string $method, array $arguments);

    public function bind(string $methodIdentifier, callable $callback) : void;

}