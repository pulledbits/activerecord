<?php

namespace pulledbits\ActiveRecord\Source;

use pulledbits\ActiveRecord\SQL\EntityType;

interface Schema
{
    public function describeTable(\pulledbits\ActiveRecord\SQL\Schema $schema, string $tableIdentifier) : EntityType;
}