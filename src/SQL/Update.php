<?php


namespace pulledbits\ActiveRecord\SQL;


class Update
{
    private $tableIdentifier;
    private $values;
    private $queryParameters;

    public function __construct(string $tableIdentifier, array $values, array $queryParameters)
    {
        $this->tableIdentifier = $tableIdentifier;
        $this->values = $values;
        $this->queryParameters = $queryParameters;
    }


    public function __toString() : string
    {
        return "UPDATE " . $this->tableIdentifier . " SET " . join(", ", $this->values) . $this->where;
    }

    public function parameters()
    {
        return array_merge($this->queryParameters, $this->where->parameters());
    }

    public function where(array $sqlConditions, array $queryParameters)
    {
        $this->where = new Where($sqlConditions, $queryParameters);
    }

}