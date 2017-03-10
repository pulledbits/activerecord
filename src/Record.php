<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 17-2-17
 * Time: 10:48
 */
namespace pulledbits\ActiveRecord;

/**
 * Interface Record
 * @package pulledbits\ActiveRecord
 */
interface Record
{
    /**
     * @param string $referenceIdentifier
     * @param string $referencedEntityTypeIdentifier
     * @param array $conditions
     * @return void
     */
    public function references(string $referenceIdentifier, string $referencedEntityTypeIdentifier, array $conditions);

    /**
     * @param array $values
     * @return void
     */
    public function contains(array $values);

    /**
     * @param array $attributeIdentifiers
     * @return void
     */
    public function requires(array $attributeIdentifiers);

    /**
     * @return bool
     */
    public function missesRequiredValues() : bool;

    /**
     * @param string $property
     */
    public function __get($property);

    /**
     * @param string $entityTypeIdentifier
     * @param array $conditions
     * @return Record[]
     */
    public function read(string $entityTypeIdentifier, array $conditions): array;

    /**
     * @param string $entityTypeIdentifier
     * @param array $conditions
     * @return Record
     */
    public function readFirst(string $entityTypeIdentifier, array $conditions): \pulledbits\ActiveRecord\Record;

    /**
     * @param string $property
     * @param string $value
     */
    public function __set($property, $value);

    /**
     * @return int
     */
    public function delete() : int;

    /**
     * @return int
     */
    public function create() : int;

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments);
}