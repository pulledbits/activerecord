<?php

namespace pulledbits\ActiveRecord\Source;

interface Schema
{
    public function describeTable(string $tableIdentifier): array;

    public function describeTables();
}