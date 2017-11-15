<?php
namespace pulledbits\ActiveRecord;

final class RecordFactory {

    private $configurator;

    public function __construct(\pulledbits\ActiveRecord\Configurator $configurator)
    {
        $this->configurator = $configurator;
    }
    public function configureRecord(Entity $record) : Entity
    {
        return $record->configure($this->configurator);
    }
}