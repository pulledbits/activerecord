<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 24-2-17
 * Time: 13:45
 */

namespace pulledbits\ActiveRecord\Source;


final class GeneratorGeneratorFactory
{
    public function makeEntityGeneratorGenerator(array $entityIdentifier, array $requiredAttributeIdentifiers) : EntityGeneratorGenerator {
        return new EntityGeneratorGenerator($entityIdentifier, $requiredAttributeIdentifiers);
    }

    public function makeWrappedEntityGeneratorGenerator(string $entityTypeIdentifier) : WrappedEntityGeneratorGenerator {
        return new WrappedEntityGeneratorGenerator($entityTypeIdentifier);
    }

    public function makeGeneratorGenerator(array $entityDescription) : GeneratorGenerator
    {
        if (array_key_exists('entityTypeIdentifier', $entityDescription)) {
            return $this->makeWrappedEntityGeneratorGenerator($entityDescription['entityTypeIdentifier']);
        }
        $entityGeneratorGenerator = $this->makeEntityGeneratorGenerator($entityDescription['identifier'], $entityDescription['requiredAttributeIdentifiers']);
        foreach ($entityDescription['references'] as $referenceIdentifier => $reference) {
            $entityGeneratorGenerator->references($referenceIdentifier, $reference['table'], $reference['where']);
        }
        return $entityGeneratorGenerator;
    }

    public function makeReference(string $entityTypeIdentifier, array $conditions) : array
    {
        return [
            'table' => $entityTypeIdentifier,
            'where' => $conditions
        ];
    }
}