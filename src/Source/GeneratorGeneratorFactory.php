<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 24-2-17
 * Time: 13:45
 */

namespace pulledbits\ActiveRecord\Source;


/**
 * Class GeneratorGeneratorFactory
 * @package pulledbits\ActiveRecord\Source
 */
final class GeneratorGeneratorFactory
{
    /**
     * @param array $entityIdentifier
     * @return EntityGeneratorGenerator
     */
    public function makeEntityGeneratorGenerator(array $entityIdentifier) : EntityGeneratorGenerator {
        return new EntityGeneratorGenerator($entityIdentifier);
    }

    /**
     * @param string $entityTypeIdentifier
     * @return WrappedEntityGeneratorGenerator
     */
    public function makeWrappedEntityGeneratorGenerator(string $entityTypeIdentifier) : WrappedEntityGeneratorGenerator {
        return new WrappedEntityGeneratorGenerator($entityTypeIdentifier);
    }

    /**
     * @param array $entityDescription
     * @return GeneratorGenerator
     */
    public function makeGeneratorGenerator(array $entityDescription) : GeneratorGenerator
    {
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