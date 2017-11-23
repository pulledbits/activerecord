<?php

namespace pulledbits\ActiveRecord\Source;

use pulledbits\ActiveRecord\RecordConfigurator;

interface Schema
{
    public function describeTable(string $tableIdentifier): RecordConfigurator;
}