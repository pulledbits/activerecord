<?php
namespace ActiveRecord;

interface RecordFactory {
    public function makeRecord(string $identifier, Schema\Asset $asset, array $values) : Record;
}