<?php

namespace pulledbits\ActiveRecord;

use pulledbits\ActiveRecord\SQL\EntityType;

interface RecordTypes
{
    public function makeRecordType(string $recordTypeIdentifier): EntityType;
}