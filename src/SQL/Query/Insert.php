<?php


namespace pulledbits\ActiveRecord\SQL\Query;


use pulledbits\ActiveRecord\SQL\Connection;

class Insert
{
    private $connection;
    private $tableIdentifier;
    private $values;

    public function __construct(Connection $connection, string $tableIdentifier, PreparedParameters $values)
    {
        $this->connection = $connection;
        $this->tableIdentifier = $tableIdentifier;
        $this->values = $values;
    }

    public function execute() : Result
    {
        return new Result($this->connection->execute("INSERT INTO " . $this->tableIdentifier . " (" . join(', ', $this->values->extractColumns()) . ") VALUES (" . join(', ', $this->values->extractParameterizedValues()) . ")", $this->values->extractParameters()));
    }
}