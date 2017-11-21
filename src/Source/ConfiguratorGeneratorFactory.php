<?php
namespace pulledbits\ActiveRecord\Source;


use pulledbits\ActiveRecord\Source\ConfiguratorGenerator\Entity;
use pulledbits\ActiveRecord\Source\ConfiguratorGenerator\WrappedEntity;

final class ConfiguratorGeneratorFactory
{
    private $sourceSchema;

    public function __construct(Schema $sourceSchema)
    {
        $this->sourceSchema = $sourceSchema;
    }

    public function makeConfiguratorGenerator(string $entityTypeIdentifier) : ConfiguratorGenerator
    {
        $entityDescription = $this->sourceSchema->describeTable($entityTypeIdentifier);
        if (array_key_exists('entityTypeIdentifier', $entityDescription)) {
            return new WrappedEntity($entityDescription['entityTypeIdentifier']);
        }
        $entityGeneratorGenerator = new Entity($entityDescription['identifier']);
        $entityGeneratorGenerator->requires($entityDescription['requiredAttributeIdentifiers']);
        foreach ($entityDescription['references'] as $referenceIdentifier => $reference) {
            $entityGeneratorGenerator->references($referenceIdentifier, $reference['table'], $reference['where']);
        }

        return $entityGeneratorGenerator;
    }

}