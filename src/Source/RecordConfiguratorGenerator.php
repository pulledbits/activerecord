<?php

namespace pulledbits\ActiveRecord\Source;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\RecordType;

interface RecordConfiguratorGenerator
{
    public function generateConfigurator(RecordType $recordFactory) : RecordConfigurator;
}