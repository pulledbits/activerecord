<?php


namespace pulledbits\ActiveRecord;


interface RecordFactory
{
    public function makeRecord() : Record;
}