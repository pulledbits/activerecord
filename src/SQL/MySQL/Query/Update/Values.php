<?php


namespace pulledbits\ActiveRecord\SQL\MySQL\Query\Update;


use pulledbits\ActiveRecord\SQL\MySQL\Query\PreparedParameters;

class Values
{
    private $values;

    public function __construct(PreparedParameters $values)
    {
        $this->values = $values;
    }

    public function __toString(): string
    {
        return " SET " . join(", ", $this->values->extractParametersSQL());
    }

    public function parameters()
    {
        return $this->values->extractParameters();
    }
}