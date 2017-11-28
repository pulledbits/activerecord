<?php

namespace pulledbits\ActiveRecord\Source;

use pulledbits\ActiveRecord\SQL\Meta\TableDescription;

interface Table
{
    public function describe(\Doctrine\DBAL\Schema\Table $dbalSchemaTable): TableDescription;

    public function makeReference(string $entityTypeIdentifier, array $conditions): array;
}