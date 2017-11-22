<?php

namespace pulledbits\ActiveRecord\Source;

interface Schema
{
    public function describeTable(string $tableIdentifier): RecordConfiguratorGenerator;

    public function describeTables();
}