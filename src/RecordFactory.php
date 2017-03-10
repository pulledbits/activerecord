<?php
namespace pulledbits\ActiveRecord;

/**
 * Class RecordFactory
 * @package pulledbits\ActiveRecord
 */
final class RecordFactory {
    /**
     * @var string
     */
    private $path;

    /**
     * RecordFactory constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param Schema $schema
     * @param string $entityTypeIdentifier
     * @return Entity
     */
    public function makeRecord(Schema $schema, string $entityTypeIdentifier) : Entity
    {
        $configurator = require $this->path . DIRECTORY_SEPARATOR . $entityTypeIdentifier . '.php';
        return $configurator($schema, $entityTypeIdentifier);
    }
}