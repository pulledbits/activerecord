<?php

namespace pulledbits\ActiveRecord\Source;

interface Table
{
    public function describe(\Doctrine\DBAL\Schema\Table $dbalSchemaTable): array;

    public function makeReference(string $entityTypeIdentifier, array $conditions): array;
}