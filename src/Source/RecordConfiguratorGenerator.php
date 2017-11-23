<?php

namespace pulledbits\ActiveRecord\Source;

use pulledbits\ActiveRecord\RecordConfigurator;

interface RecordConfiguratorGenerator
{
    public function generateConfigurator() : RecordConfigurator;
}