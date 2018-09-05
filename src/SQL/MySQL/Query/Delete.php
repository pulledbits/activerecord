<?php


namespace pulledbits\ActiveRecord\SQL\MySQL\Query;


use pulledbits\ActiveRecord\SQL\Connection;
use pulledbits\ActiveRecord\SQL\MySQL\QueryFactory;

class Delete implements \pulledbits\ActiveRecord\SQL\Query
{
    private $tableIdentifier;

    /**
     * @var Where
     */
    private $where;
    private $connection;

    public function __construct(Connection $connection, string $tableIdentifier)
    {
        $this->connection = $connection;
        $this->tableIdentifier = $tableIdentifier;
    }

    public function where(array $where)
    {
        $this->where = QueryFactory::makeWhere($where);
    }

    public function execute(): \pulledbits\ActiveRecord\Result
    {
        return $this->connection->execute("DELETE FROM " . $this->tableIdentifier . $this->where, $this->where->parameters());
    }
}