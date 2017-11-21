<?php

namespace pulledbits\ActiveRecord\Source;

use Psr\Http\Message\StreamInterface;

final class WrappedEntityConfiguratorGenerator implements ConfiguratorGenerator
{
    private $entityTypeIdentifier;

    public function __construct(string $entityTypeIdentifier)
    {
        $this->entityTypeIdentifier = $entityTypeIdentifier;
    }

    public function generate(StreamInterface $stream) : void
    {
        $stream->write('<?php return $this->generate("' . $this->entityTypeIdentifier . '");');
    }
}