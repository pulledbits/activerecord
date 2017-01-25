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

    public function makeRecord(Schema\EntityType $asset, array $values) : Entity
    {
        return $asset->executeEntityConfigurator($this->path, $values);
    }
}