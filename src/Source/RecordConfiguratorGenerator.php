<?php

namespace pulledbits\ActiveRecord\Source;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\RecordFactory;

interface RecordConfiguratorGenerator
{
    public function generateConfigurator(RecordFactory $recordFactory) : RecordConfigurator;
}