<?php

namespace pulledbits\ActiveRecord\Source;

interface Table
{
    public function describe(\Doctrine\DBAL\Schema\Table $dbalSchemaTable): TableDescription;

    public function makeReference(string $entityTypeIdentifier, array $conditions): array;
}