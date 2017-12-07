<?php
namespace pulledbits\ActiveRecord;

interface Record
{
    public function contains(array $values);

    public function __get($property);

    public function __set($property, $value);

    public function delete() : int;

    public function create() : int;

    public function __call(string $method, array $arguments);

}