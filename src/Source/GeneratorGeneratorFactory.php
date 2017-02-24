<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 24-2-17
 * Time: 13:45
 */

namespace pulledbits\ActiveRecord\Source;


class GeneratorGeneratorFactory
{

    /**
     * GeneratorGeneratorFactory constructor.
     */
    public function __construct()
    {
    }

    public function makeEntityGeneratorGenerator(array $entityIdentifier, array $requiredAttributeIdentifiers, array $references) {
        return new EntityGeneratorGenerator($entityIdentifier, $requiredAttributeIdentifiers, $references);
    }

    public function makeWrappedEntityGeneratorGenerator(string $entityTypeIdentifier) {
        return new WrappedEntityGeneratorGenerator($entityTypeIdentifier);
    }

    public function makeGeneratorGenerator(array $entityDescription)
    {
        if (array_key_exists('entityTypeIdentifier', $entityDescription)) {
            return $this->makeWrappedEntityGeneratorGenerator($entityDescription['entityTypeIdentifier']);
        }
        return $this->makeEntityGeneratorGenerator($entityDescription['identifier'], $entityDescription['requiredColumnIdentifiers'], $entityDescription['references']);
    }
}