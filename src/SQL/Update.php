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

    public function where(PreparedParameters $preparedParameters) : void
    {
        $this->where = new Where($preparedParameters);
    }

    public function execute(Connection $connection) : int
    {
        $parameters = $this->values->parameters();
        if ($this->where !== null) {
            $parameters = array_merge($parameters, $this->where->parameters());
        }
        return $connection->executeChange("UPDATE " . $this->tableIdentifier . $this->values . $this->where, $parameters);
    }

}