<?php


namespace pulledbits\ActiveRecord\SQL;


class Update
{
    private $tableIdentifier;
    private $values;

    public function __construct(string $tableIdentifier, Update\Values $values)
    {
        $this->tableIdentifier = $tableIdentifier;
        $this->values = $values;
    }


    public function __toString() : string
    {
        return "UPDATE " . $this->tableIdentifier . $this->values . $this->where;
    }

    public function parameters()
    {
        return array_merge($this->values->parameters(), $this->where->parameters());
    }

    public function where(array $sqlConditions, array $queryParameters)
    {
        $this->where = new Where($sqlConditions, $queryParameters);
    }

}