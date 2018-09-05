<?php


namespace pulledbits\ActiveRecord\SQL\MySQL\Query;


use pulledbits\ActiveRecord\SQL\Connection;
use pulledbits\ActiveRecord\SQL\MySQL\QueryFactory;

class Update implements \pulledbits\ActiveRecord\SQL\Query
{
    private $tableIdentifier;
    private $values;

    /**
     * @var Where
     */
    private $where;
    private $connection;

    public function __construct(Connection $connection, string $tableIdentifier, Update\Values $values)
    {
        $this->connection = $connection;
        $this->tableIdentifier = $tableIdentifier;
        $this->values = $values;
    }

    public function where(array $where)
    {
        $this->where = QueryFactory::makeWhere($where);
    }

    public function execute(): \pulledbits\ActiveRecord\Result
    {
        $parameters = $this->values->parameters();
        if ($this->where !== null) {
            $parameters = array_merge($parameters, $this->where->parameters());
        }
        return $this->connection->execute("UPDATE " . $this->tableIdentifier . $this->values . $this->where, $parameters);
    }

}