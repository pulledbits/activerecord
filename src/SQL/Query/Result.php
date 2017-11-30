<?php
namespace pulledbits\ActiveRecord\SQL\Query;

use pulledbits\ActiveRecord\RecordType;

class Result implements \Countable
{

    private $statement;

    public function __construct(\pulledbits\ActiveRecord\SQL\Statement $statement)
    {
        $this->statement = $statement;
    }

    public function count()
    {
        return $this->statement->rowCount();
    }

    public function fetchAll() : array
    {
        return $this->statement->fetchAll();
    }
}