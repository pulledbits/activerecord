<?php


namespace pulledbits\ActiveRecord\SQL\Query;


class Result implements \Countable
{

    private $statement;

    public function __construct(\pulledbits\ActiveRecord\SQL\Statement $statement)
    {
        $this->statement = $statement;
    }

    public function map(callable $callback)
    {
        return array_map($callback, $this->statement->fetchAll());
    }

    public function count()
    {
        return $this->statement->rowCount();
    }
}