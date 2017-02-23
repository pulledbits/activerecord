<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 21-2-17
 * Time: 14:49
 */

namespace pulledbits\ActiveRecord\Record;

class FreshTest extends \PHPUnit_Framework_TestCase
{

    public function testDecoratedMethods_When_Called_Expect_ResultsFromWrappedRecord() {

        $calls = [];
        $wrappedRecord = new class($calls) implements \pulledbits\ActiveRecord\Record {

            private $calls;
            private $values;
            private $requiredAttributeIdentifiers;
            private $references;

            public function __construct(array &$calls)
            {
                $this->calls =& $calls;
                $this->values = [];
                $this->requiredAttributeIdentifiers = [];
                $this->references = [];
            }


            public function references(string $referenceIdentifier, string $referencedEntityTypeIdentifier, array $conditions) {
                $this->references[$referenceIdentifier] =  [$referencedEntityTypeIdentifier, $conditions];
            }

            public function contains(array $values)
            {
                $this->values += $values;
            }

            public function requires(array $attributeIdentifiers)
            {
                $this->requiredAttributeIdentifiers = $attributeIdentifiers;
            }

            public function missesRequiredValues(): bool
            {
                foreach ($this->requiredAttributeIdentifiers as $attributeIdentifier) {
                    if (array_key_exists($attributeIdentifier, $this->values) === false) {
                        return true;
                    } elseif ($this->values[$attributeIdentifier] === null) {
                        return true;
                    }
                }
                return false;
            }

            /**
             * @param string $property
             */
            public function __get($property)
            {
                return 'bla';
            }

            public function read(string $entityTypeIdentifier, array $conditions): array
            {
                return [$this];
            }

            public function readFirst(string $entityTypeIdentifier, array $conditions): \pulledbits\ActiveRecord\Record
            {
                return $this;
            }

            /**
             * @param string $property
             * @param string $value
             */
            public function __set($property, $value)
            {
                $this->calls['__set'] = func_get_args();
                $this->values[$property] = $value;
            }

            /**
             */
            public function delete() : int
            {
                return 42;
            }

            public function create() : int
            {
                return $this->missesRequiredValues() ? 43 : 1;
            }

            public function __call(string $method, array $arguments)
            {
                if (array_key_exists(substr($method, 7), $this->references)) {
                    return [$this];
                } else {
                    return [];
                }
            }
        };
        $object = new Fresh($wrappedRecord);

        $this->assertEquals([$wrappedRecord], $object->read('entity', []));
        $this->assertEquals($wrappedRecord, $object->readFirst('entity', []));

        $this->assertFalse($wrappedRecord->missesRequiredValues());
        $object->requires(['bla']);
        $this->assertTrue($wrappedRecord->missesRequiredValues());

        $this->assertTrue($object->missesRequiredValues());
        $object->bla = 'bloe';
        $this->assertFalse($object->missesRequiredValues());
        $object->bla = 'blie';
        $this->assertEquals(['bla', 'blie'], $calls['__set']);

        $this->assertEquals(42, $object->delete());
        $this->assertEquals(1, $object->create());

        $this->assertEquals([], $wrappedRecord->__call('fetchByforeignkey', []));
        $object->references('foreignkey', 'somewhere', ['bla']);
        $this->assertEquals([$wrappedRecord], $wrappedRecord->__call('fetchByforeignkey', []));
    }

}
