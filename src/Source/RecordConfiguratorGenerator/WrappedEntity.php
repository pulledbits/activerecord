<?php

namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

use Psr\Http\Message\StreamInterface;
use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\RecordFactory;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

final class WrappedEntity implements RecordConfiguratorGenerator
{
    private $wrappedEntityGenerator;

    public function __construct(\pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator $wrappedEntityGenerator)
    {
        $this->wrappedEntityGenerator = $wrappedEntityGenerator;
    }

    public function generateConfigurator(RecordFactory $recordFactory) : RecordConfigurator
    {
        return $this->wrappedEntityGenerator->generateConfigurator($recordFactory);
    }
}