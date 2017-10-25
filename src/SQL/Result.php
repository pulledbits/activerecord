<?php


namespace pulledbits\ActiveRecord\SQL;


class Result implements \Countable
{

    private $statement;

    public function __construct(\PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    public function map(callable $callback)
    {
        return array_map($callback, $this->statement->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function count()
    {
        return $this->statement->rowCount();
    }
}