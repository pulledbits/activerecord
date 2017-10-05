<?php

namespace pulledbits\ActiveRecord\Source;

interface Schema
{
    public function describeTable(SQL\Table $sourceTable, string $tableIdentifier): array;

    public function describeTables(SQL\Table $sourceTable);
}