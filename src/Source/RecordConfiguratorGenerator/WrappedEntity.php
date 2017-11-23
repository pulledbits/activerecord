<?php

namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

final class WrappedEntity implements RecordConfiguratorGenerator
{
    private $wrappedEntityGenerator;

    public function __construct(\pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator $wrappedEntityGenerator)
    {
        $this->wrappedEntityGenerator = $wrappedEntityGenerator;
    }

    public function generateConfigurator() : RecordConfigurator
    {
        return $this->wrappedEntityGenerator->generateConfigurator();
    }
}