<?php

namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

use Psr\Http\Message\StreamInterface;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

final class WrappedEntity implements RecordConfiguratorGenerator
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