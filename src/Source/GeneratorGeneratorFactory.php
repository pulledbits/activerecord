<?php
namespace pulledbits\ActiveRecord\Source;


final class GeneratorGeneratorFactory
{
    private $sourceSchema;

    public function __construct(Schema $sourceSchema)
    {
        $this->sourceSchema = $sourceSchema;
    }

    public function makeGeneratorGenerator(string $entityTypeIdentifier) : GeneratorGenerator
    {
        $entityDescription = $this->sourceSchema->describeTable($entityTypeIdentifier);
        if (array_key_exists('entityTypeIdentifier', $entityDescription)) {
            return new WrappedEntityGeneratorGenerator($entityDescription['entityTypeIdentifier']);
        }
        $entityGeneratorGenerator = new EntityGeneratorGenerator($entityDescription['identifier']);
        $entityGeneratorGenerator->requires($entityDescription['requiredAttributeIdentifiers']);
        foreach ($entityDescription['references'] as $referenceIdentifier => $reference) {
            $entityGeneratorGenerator->references($referenceIdentifier, $reference['table'], $reference['where']);
        }

        return $entityGeneratorGenerator;
    }

}