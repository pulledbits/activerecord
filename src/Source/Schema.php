<?php

namespace pulledbits\ActiveRecord\Source;

use pulledbits\ActiveRecord\Entity;

interface Schema
{
    public function makeRecord(string $tableIdentifier): Entity;
}