<?php


namespace pulledbits\ActiveRecord\SQL\Query;


use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\RecordFactory;

class Result implements \Countable
{

    private $statement;
    private $configurator;

    public function __construct(\pulledbits\ActiveRecord\SQL\Statement $statement, RecordConfigurator $configurator = null)
    {
        $this->statement = $statement;
        $this->configurator = $configurator;
    }

    public function count()
    {
        return $this->statement->rowCount();
    }

    public function fetchAllAs() : array
    {
        $records = [];
        foreach ($this->statement->fetchAll() as $row) {
            $record = $this->configurator->configure();
            $record->contains($row);
            $records[] = $record;
        }
        return $records;
    }
}