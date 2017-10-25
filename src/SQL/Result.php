<?php


namespace pulledbits\ActiveRecord\SQL;


class Result
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
}