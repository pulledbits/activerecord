<?php

namespace pulledbits\ActiveRecord\Source;

interface Schema
{
    public function describeTable(Table $sourceTable, string $tableIdentifier): array;

    public function describeTables(Table $sourceTable);
}