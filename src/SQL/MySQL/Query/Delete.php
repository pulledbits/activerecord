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

    public function __construct(string $tableIdentifier)
    {
        $this->tableIdentifier = $tableIdentifier;
    }

    public function where(array $where)
    {
        $this->where = QueryFactory::makeWhere($where);
    }

    public function execute(Connection $connection): \pulledbits\ActiveRecord\Result
    {
        return $connection->execute("DELETE FROM " . $this->tableIdentifier . $this->where, $this->where->parameters());
    }
}