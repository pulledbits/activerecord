<?php

namespace pulledbits\ActiveRecord\Source\ConfiguratorGenerator;

use Psr\Http\Message\StreamInterface;
use pulledbits\ActiveRecord\Source\ConfiguratorGenerator;

final class WrappedEntity implements ConfiguratorGenerator
{
    private $entityTypeIdentifier;

    public function __construct(string $entityTypeIdentifier)
    {
        $this->entityTypeIdentifier = $entityTypeIdentifier;
    }

    public function generateConfigurator(StreamInterface $stream) : void
    {
        $stream->write('<?php return $this->generate("' . $this->entityTypeIdentifier . '");');
    }
}