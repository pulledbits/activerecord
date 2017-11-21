<?php


namespace pulledbits\ActiveRecord;


interface RecordConfigurator
{
    public function configure() : Record;
}