<?php

namespace pulledbits\ActiveRecord;


class ConfiguratorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Schema
     */
    private $object;

    protected function setUp()
    {
        $sourceSchema = new \pulledbits\ActiveRecord\SQL\Meta\Schema([
            'activity' => [
                'identifier' => [],
                'requiredAttributeIdentifiers' => [],
                'references' => []
            ]
        ],[]);
        $this->object = $sourceSchema->createConfigurator(sys_get_temp_dir());
    }

    public function testGenerate_When_ExistingEntity_Expect_SourceFileGenerated()
    {
        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'activity.php';
        unlink($file);
        $this->assertFileNotExists($file);
        $this->object->generate(new class implements RecordFactory{

            public function makeRecord(): Record
            {
                return new class implements Record {

                    public function identifiedBy(array $primaryKey)
                    {
                    }

                    public function references(string $referenceIdentifier, string $referencedEntityTypeIdentifier, array $conditions)
                    {
                    }

                    public function contains(array $values)
                    {
                    }

                    public function requires(array $attributeIdentifiers)
                    {
                    }

                    public function missesRequiredValues(): bool
                    {
                    }

                    public function __get($property)
                    {
                    }

                    public function read(string $entityTypeIdentifier, array $conditions): array
                    {
                    }

                    public function __set($property, $value)
                    {
                    }

                    public function delete(): int
                    {
                    }

                    public function create(): int
                    {
                    }

                    public function __call(string $method, array $arguments)
                    {
                    }
                };
            }
        }, 'activity');
        $this->assertFileExists($file);
        $this->assertEquals('<?php namespace pulledbits\\ActiveRecord;' . PHP_EOL, fgets(fopen($file, 'r')));
    }
}
