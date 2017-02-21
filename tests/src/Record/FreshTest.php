<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 21-2-17
 * Time: 14:49
 */

namespace ActiveRecord\Record;


use ActiveRecord\Entity;

class FreshTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Fresh
     */
    private $object;

    protected function setUp() {

        $wrappedRecord = new class implements \ActiveRecord\Record {

            public function references(string $referenceIdentifier, string $referencedEntityTypeIdentifier, array $conditions) {
                // TODO: Implement references() method.
            }

            public function contains(array $values)
            {
                // TODO: Implement contains() method.
            }

            public function requires(array $columnIdentifiers)
            {
                // TODO: Implement requires() method.
            }

            public function missesRequiredValues(): bool
            {
                // TODO: Implement missesRequiredValues() method.
            }

            /**
             * @param string $property
             */
            public function __get($property)
            {
                // TODO: Implement __get() method.
            }

            public function read(string $entityTypeIdentifier, array $conditions): array
            {
                return ['bla'];
            }

            public function readFirst(string $entityTypeIdentifier, array $conditions): \ActiveRecord\Record
            {
                // TODO: Implement readFirst() method.
            }

            /**
             * @param string $property
             * @param string $value
             */
            public function __set($property, $value)
            {
                // TODO: Implement __set() method.
            }

            /**
             */
            public function delete()
            {
                // TODO: Implement delete() method.
            }

            public function create()
            {
                // TODO: Implement create() method.
            }

            public function __call(string $method, array $arguments)
            {
                // TODO: Implement __call() method.
            }
        };
        $this->object = new Fresh($wrappedRecord);
    }

    public function testRead_When_Called_Expect_ResultsFromWrappedRecord() {

        $this->assertEquals(['bla'], $this->object->read('entity', []));
    }

}
