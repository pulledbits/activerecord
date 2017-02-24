<?php

namespace pulledbits\ActiveRecord\Source;


class WrappedEntityGeneratorGenerator implements GeneratorGenerator
{
    private $entityTypeIdentifier;

    /**
     * WrappedEntityGenerator constructor.
     */
    public function __construct(string $entityTypeIdentifier)
    {
        $this->entityTypeIdentifier = $entityTypeIdentifier;
    }

    public function generate()
    {
        return '<?php return require __DIR__ . DIRECTORY_SEPARATOR . "' . $this->entityTypeIdentifier . '.php";';
    }
}