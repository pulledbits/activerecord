<?php

namespace pulledbits\ActiveRecord;

use pulledbits\ActiveRecord\SQL\MySQL\EntityType;

interface RecordTypes
{
    public function makeRecordType(string $recordTypeIdentifier): EntityType;
}