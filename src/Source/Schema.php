<?php

namespace pulledbits\ActiveRecord\Source;

use pulledbits\ActiveRecord\SQL\EntityType;

interface Schema
{
    public function describeTable(string $tableIdentifier) : EntityType;
}