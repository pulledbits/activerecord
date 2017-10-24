<?php


namespace pulledbits\ActiveRecord\SQL\Update;


class Values
{
    private $values;
    private $queryParameters;

    public function __construct($values, $queryParameters)
    {
        $this->values = $values;
        $this->queryParameters = $queryParameters;
    }

    public function __toString() : string
    {
        return " SET " . join(", ", $this->values);
    }

    public function parameters()
    {
        return $this->queryParameters;
    }
}