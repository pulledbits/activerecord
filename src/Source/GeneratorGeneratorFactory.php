<?php
namespace pulledbits\ActiveRecord\Source;


final class GeneratorGeneratorFactory
{
    private $sourceSchema;

    public function __construct(Schema $sourceSchema)
    {
        $this->sourceSchema = $sourceSchema;
    }

    public function makeEntityGeneratorGenerator(array $entityIdentifier) : EntityGeneratorGenerator {
        return new EntityGeneratorGenerator($entityIdentifier);
    }

    public function makeWrappedEntityGeneratorGenerator(string $entityTypeIdentifier) : WrappedEntityGeneratorGenerator {
        return new WrappedEntityGeneratorGenerator($entityTypeIdentifier);
    }

    public function makeGeneratorGenerator(string $entityTypeIdentifier) : GeneratorGenerator
    {
        $entityDescription = $this->sourceSchema->describeTable($entityTypeIdentifier);
        if (array_key_exists('entityTypeIdentifier', $entityDescription)) {
            return $this->makeWrappedEntityGeneratorGenerator($entityDescription['entityTypeIdentifier']);
        }
        $entityGeneratorGenerator = $this->makeEntityGeneratorGenerator($entityDescription['identifier']);
        $entityGeneratorGenerator->requires($entityDescription['requiredAttributeIdentifiers']);
        foreach ($entityDescription['references'] as $referenceIdentifier => $reference) {
            $entityGeneratorGenerator->references($referenceIdentifier, $reference['table'], $reference['where']);
        }

        return $entityGeneratorGenerator;
    }

}