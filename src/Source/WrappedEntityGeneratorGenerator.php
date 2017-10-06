<?php

namespace pulledbits\ActiveRecord\Source;


final class WrappedEntityGeneratorGenerator implements GeneratorGenerator
{
    private $entityTypeIdentifier;

    public function __construct(string $entityTypeIdentifier)
    {
        $this->entityTypeIdentifier = $entityTypeIdentifier;
    }

    public function generate()
    {
        return '<?php return $this->generateConfigurator("' . $this->entityTypeIdentifier . '");';
    }
}