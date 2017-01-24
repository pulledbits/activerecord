<?php
namespace ActiveRecord;

interface RecordFactory {
    public function makeRecord(Schema\Asset $asset, array $values) : Record;
}