<?php


namespace pulledbits\ActiveRecord\SQL\Query;


use pulledbits\ActiveRecord\Configurator;
use pulledbits\ActiveRecord\Entity;
use pulledbits\ActiveRecord\RecordFactory;
use pulledbits\ActiveRecord\SQL\Schema;

class Result implements \Countable
{

    private $statement;
    private $configurator;

    public function __construct(\pulledbits\ActiveRecord\SQL\Statement $statement, Configurator $configurator = null)
    {
        $this->statement = $statement;
        $this->configurator = $configurator;
    }

    public function count()
    {
        return $this->statement->rowCount();
    }

    public function fetchAllAs(Entity $recordPrototype) : array
    {
        $records = [];
        foreach ($this->statement->fetchAll() as $row) {
            $record = clone $recordPrototype;
            $record = $record->configure($this->configurator);
            $record->contains($row);
            $records[] = $record;
        }
        return $records;
    }
}