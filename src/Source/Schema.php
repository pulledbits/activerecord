<?php

namespace pulledbits\ActiveRecord\Source;

use pulledbits\ActiveRecord\SQL\Source\Table;

interface Schema
{
    public function describeTable(Table $sourceTable, string $tableIdentifier): array;

    public function describeTables(Table $sourceTable);
}