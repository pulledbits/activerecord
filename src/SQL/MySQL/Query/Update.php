<?php


namespace pulledbits\ActiveRecord\SQL\MySQL\Query;


use pulledbits\ActiveRecord\SQL\Connection;
use pulledbits\ActiveRecord\SQL\MySQL\QueryFactory;

class Update implements \pulledbits\ActiveRecord\SQL\MySQL\Query
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

    public function where(array $where)
    {
        $this->where = QueryFactory::makeWhere($where);
    }

    public function execute(Connection $connection): Result
    {
        $parameters = $this->values->parameters();
        if ($this->where !== null) {
            $parameters = array_merge($parameters, $this->where->parameters());
        }
        return $connection->execute("UPDATE " . $this->tableIdentifier . $this->values . $this->where, $parameters);
    }

}