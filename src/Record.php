<?php
namespace pulledbits\ActiveRecord;

interface Record
{
    public function references(string $referenceIdentifier, string $referencedEntityTypeIdentifier, array $conditions);

    public function contains(array $values);

    public function missesRequiredValues() : bool;

    public function __get($property);

    public function read(string $entityTypeIdentifier, array $conditions): array;

    public function __set($property, $value);

    public function delete() : int;

    public function create() : int;

    public function __call(string $method, array $arguments);

}