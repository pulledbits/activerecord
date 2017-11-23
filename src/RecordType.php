<?php


namespace pulledbits\ActiveRecord;


interface RecordType
{
    public function makeRecord() : Record;
}