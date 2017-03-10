<?php

namespace pulledbits\ActiveRecord\Source;


/**
 * Class WrappedEntityGeneratorGenerator
 * @package pulledbits\ActiveRecord\Source
 */
final class WrappedEntityGeneratorGenerator implements GeneratorGenerator
{
    /**
     * @var string
     */
    private $entityTypeIdentifier;

    /**
     * WrappedEntityGenerator constructor.
     */
    public function __construct(string $entityTypeIdentifier)
    {
        $this->entityTypeIdentifier = $entityTypeIdentifier;
    }

    /**
     * @return string
     */
    public function generate()
    {
        return '<?php return require __DIR__ . DIRECTORY_SEPARATOR . "' . $this->entityTypeIdentifier . '.php";';
    }
}