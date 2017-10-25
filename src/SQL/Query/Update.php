<?php


namespace pulledbits\ActiveRecord\SQL\Query;


use pulledbits\ActiveRecord\SQL\Connection;
use pulledbits\ActiveRecord\SQL\Query;

class Update
{
    private $connection;
    private $tableIdentifier;
    private $values;

    /**
     * @var Where
     */
    private $where;

    public function __construct(Connection $connection, string $tableIdentifier, Query\Update\Values $values)
    {
        $this->connection = $connection;
        $this->tableIdentifier = $tableIdentifier;
        $this->values = $values;
    }

    public function where(Where $where)
    {
        $this->where = $where;
    }

    public function execute() : Result
    {
        $parameters = $this->values->parameters();
        if ($this->where !== null) {
            $parameters = array_merge($parameters, $this->where->parameters());
        }
        return $this->connection->execute("UPDATE " . $this->tableIdentifier . $this->values . $this->where, $parameters);
    }

}