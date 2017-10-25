<?php


namespace pulledbits\ActiveRecord\SQL;


class Insert
{

    private $tableIdentifier;
    private $values;

    public function __construct(string $tableIdentifier, PreparedParameters $values)
    {
        $this->tableIdentifier = $tableIdentifier;
        $this->values = $values;
    }

    public function execute(Connection $connection) : Result
    {
        return $connection->execute("INSERT INTO " . $this->tableIdentifier . " (" . join(', ', $this->values->extractColumns()) . ") VALUES (" . join(', ', $this->values->extractParameterizedValues()) . ")", $this->values->extractParameters());
    }
}