<?php
namespace pulledbits\ActiveRecord\SQL\Query;

use pulledbits\ActiveRecord\RecordType;

class Result implements \Countable
{

    private $statement;
    private $recordType;

    public function __construct(\pulledbits\ActiveRecord\SQL\Statement $statement, RecordType $recordType = null)
    {
        $this->statement = $statement;
        $this->recordType = $recordType;
    }

    public function count()
    {
        return $this->statement->rowCount();
    }

    public function fetchAllAs() : array
    {
        $records = [];
        foreach ($this->statement->fetchAll() as $row) {
            $record = $this->recordType->makeRecord();
            $record->contains($row);
            $records[] = $record;
        }
        return $records;
    }
}