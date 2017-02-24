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
    public function makeEntityGeneratorGenerator(array $entityIdentifier, array $requiredAttributeIdentifiers, array $references) : EntityGeneratorGenerator {
        return new EntityGeneratorGenerator($entityIdentifier, $requiredAttributeIdentifiers, $references);
    }

    public function makeWrappedEntityGeneratorGenerator(string $entityTypeIdentifier) : WrappedEntityGeneratorGenerator {
        return new WrappedEntityGeneratorGenerator($entityTypeIdentifier);
    }

    public function makeGeneratorGenerator(array $entityDescription) : GeneratorGenerator
    {
        if (array_key_exists('entityTypeIdentifier', $entityDescription)) {
            return $this->makeWrappedEntityGeneratorGenerator($entityDescription['entityTypeIdentifier']);
        }
        return $this->makeEntityGeneratorGenerator($entityDescription['identifier'], $entityDescription['requiredAttributeIdentifiers'], $entityDescription['references']);
    }

    public function makeReference(string $entityTypeIdentifier, array $conditions) : array
    {
        return [
            'table' => $entityTypeIdentifier,
            'where' => $conditions
        ];
    }
}