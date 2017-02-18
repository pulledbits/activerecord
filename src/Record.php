<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 17-2-17
 * Time: 10:48
 */
namespace ActiveRecord;

interface Record
{
    public function references(string $referenceIdentifier, string $referencedEntityTypeIdentifier, array $conditions);

    public function contains(array $values);

    public function requires(array $columnIdentifiers);

    /**
     * @param string $property
     */
    public function __get($property);

    public function read(string $entityTypeIdentifier, array $conditions): array;

    public function readFirst(string $entityTypeIdentifier, array $conditions): Entity;

    /**
     * @param string $property
     * @param string $value
     */
    public function __set($property, $value);

    /**
     */
    public function delete();

    public function create();

    public function __call(string $method, array $arguments);
}