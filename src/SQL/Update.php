<?php


namespace pulledbits\ActiveRecord\SQL;


class Update
{
    private $tableIdentifier;
    private $values;
    private $parameters;

    public function __construct(string $tableIdentifier, array $values, array $parameters)
    {
        $this->tableIdentifier = $tableIdentifier;
        $this->values = $values;
        $this->parameters = $parameters;
    }


    public function __toString() : string
    {
        return "UPDATE " . $this->tableIdentifier . " SET " . join(", ", $this->values);
    }

    public function parameters()
    {
        return $this->parameters;
    }

}