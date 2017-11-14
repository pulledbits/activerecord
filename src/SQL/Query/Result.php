<?php


namespace pulledbits\ActiveRecord\SQL\Query;


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

    public function map(callable $callback)
    {
        return array_map($callback, $this->statement->fetchAll());
    }

    public function count()
    {
        return $this->statement->rowCount();
    }

    private function makeRecord(Schema $schema, string $entityTypeIdentifier, array $values) {
        $record = $this->recordFactory->makeRecord($schema, $entityTypeIdentifier);
        $record->contains($values);
        return $record;
    }

    public function fetchAllAs(Schema $schema, string $entityTypeIdentifier) : array
    {
        return $this->map(function(array $values) use ($schema, $entityTypeIdentifier) {
            return $this->makeRecord($schema, $entityTypeIdentifier, $values);
        });
    }

    public function fetchFirstAs(Schema $schema, $entityTypeIdentifier, array $conditions) : \pulledbits\ActiveRecord\Record
    {
        $records = $this->fetchAllAs($schema, $entityTypeIdentifier);
        if (count($records) === 0) {
            $record = $this->makeRecord($schema, $entityTypeIdentifier, $conditions);
            return new \pulledbits\ActiveRecord\Record\Fresh($record);
        }
        return $records[0];
    }
}