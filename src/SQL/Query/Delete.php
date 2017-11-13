<?php


namespace pulledbits\ActiveRecord\SQL\Query;


use pulledbits\ActiveRecord\SQL\Connection;

class Delete
{
    private $connection;
    private $tableIdentifier;

    /**
     * @var Where
     */
    private $where;

    public function __construct(Connection $connection, string $tableIdentifier)
    {
        $this->connection = $connection;
        $this->tableIdentifier = $tableIdentifier;
    }

    public function where(Where $where)
    {
        $this->where = $where;
    }

    public function execute() : Result
    {
        return new Result($this->connection->execute("DELETE FROM " . $this->tableIdentifier . $this->where, $this->where->parameters()));
    }
}