<?php

namespace pulledbits\ActiveRecord;

interface RecordTypes
{
    public function makeRecordType(string $recordTypeIdentifier): RecordType;
}