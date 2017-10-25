<?php


namespace pulledbits\ActiveRecord\SQL;


class Update
{
    private $tableIdentifier;
    private $values;

    /**
     * @var Where
     */
    private $where;

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
        $parameters = $this->values->parameters();
        if ($this->where !== null) {
            $parameters = array_merge($parameters, $this->where->parameters());
        }
        return $parameters;
    }

    public function where(PreparedParameters $preparedParameters)
    {
        $this->where = new Where($preparedParameters);
    }

}