<?php
namespace pulledbits\ActiveRecord;

final class RecordFactory {

    private $configurator;

    public function __construct(\pulledbits\ActiveRecord\Configurator $configurator)
    {
        $this->configurator = $configurator;
    }
    public function makeRecord(Entity $record) : Entity
    {
        $configurator = $record->generateConfigurator($this->configurator);
        return $configurator($record);
    }
}