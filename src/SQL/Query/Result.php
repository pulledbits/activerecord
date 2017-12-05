<?php
namespace pulledbits\ActiveRecord\SQL\Query;

class Result implements \pulledbits\ActiveRecord\Result
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