<?php

namespace pulledbits\ActiveRecord\Source;

use pulledbits\ActiveRecord\RecordConfigurator;

interface Schema
{
    public function describeTable(\pulledbits\ActiveRecord\SQL\Schema $schema, string $tableIdentifier): RecordConfigurator;
}