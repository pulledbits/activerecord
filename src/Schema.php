<?php
namespace ActiveRecord;

interface Schema {
    /**
     * @param string $entityTypeIdentifier
     * @param array $values
     * @return \ActiveRecord\Record
     */
    public function initializeRecord(string $entityTypeIdentifier, array $values) : Record;

    /**
     * @param string $entityTypeIdentifier
     * @param array $attributeIdentifiers
     * @param array $conditions
     * @return \ActiveRecord\Record[]
     */
    public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions) : array;

    /**
     * @param string $entityTypeIdentifier
     * @param array $attributeIdentifiers
     * @param array $conditions
     * @return \ActiveRecord\Record
     */
    public function readFirst(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions) : Record;

    /**
     * @param string $entityTypeIdentifier
     * @param array $values
     * @param array $conditions
     * @return int
     */
    public function update(string $entityTypeIdentifier, array $values, array $conditions) : int;

    /**
     * @param string $entityTypeIdentifier
     * @param array $values
     * @return int
     */
    public function create(string $entityTypeIdentifier, array $values) : int;

    /**
     * @param string $entityTypeIdentifier
     * @param array $conditions
     * @return int
     */
    public function delete(string $entityTypeIdentifier, array $conditions) : int;
}