<?php

namespace pulledbits\ActiveRecord;

interface RecordTypes
{
    public function makeEntityType(string $recordTypeIdentifier): EntityType;
}