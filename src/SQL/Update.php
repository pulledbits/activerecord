<?php


namespace pulledbits\ActiveRecord\SQL;


class Update
{
    private $tableIdentifier;
    private $values;

    public function __construct(string $tableIdentifier, array $values)
    {
        $this->tableIdentifier = $tableIdentifier;
        $this->values = $values;
    }


    public function __toString()
    {
        return "UPDATE " . $this->tableIdentifier . " SET " . join(", ", $this->values);
    }

}