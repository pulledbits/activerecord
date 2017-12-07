<?php

namespace pulledbits\ActiveRecord\Source;

use pulledbits\ActiveRecord\Record;

interface Schema
{
    public function makeRecord(string $tableIdentifier): Record;
}