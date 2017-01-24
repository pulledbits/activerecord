<?php
namespace ActiveRecord;

class RecordFactory {
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function makeRecord(Schema\Asset $asset, array $values) : Record
    {
        return $asset->executeRecordClassConfigurator($this->path, $values);
    }
}