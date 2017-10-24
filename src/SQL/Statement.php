<?php


namespace pulledbits\ActiveRecord\SQL;


class Statement
{
    private $statement;

    public function __construct(\PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    public function addParameters(array $namedParameters): void
    {
        foreach ($namedParameters as $namedParameter => $value) {
            $this->statement->bindValue($namedParameter, $value, \PDO::PARAM_STR);
        }
    }
}