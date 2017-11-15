<?php


namespace pulledbits\ActiveRecord\SQL\Query;


use pulledbits\ActiveRecord\Entity;
use pulledbits\ActiveRecord\RecordFactory;
use pulledbits\ActiveRecord\SQL\Schema;

class Result implements \Countable
{

    private $statement;

    public function __construct(\pulledbits\ActiveRecord\SQL\Statement $statement, RecordFactory $recordFactory = null)
    {
        $this->statement = $statement;
        $this->recordFactory = $recordFactory;
    }

    public function count()
    {
        return $this->statement->rowCount();
    }

    private function makeRecord(Entity $record, array $values) {
        $record = $this->recordFactory->makeRecord($record);
        $record->contains($values);
        return $record;
    }

    public function fetchAllAs(Entity $record) : array
    {
        return array_map(function(array $values) use ($record) {
            return $this->makeRecord($record, $values);
        }, $this->statement->fetchAll());
    }
}