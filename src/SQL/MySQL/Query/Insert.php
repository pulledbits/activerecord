<?php

namespace pulledbits\ActiveRecord\SQL\MySQL\Query;

use pulledbits\ActiveRecord\SQL\Connection;

class Insert implements \pulledbits\ActiveRecord\SQL\Query
{
    private $tableIdentifier;
    private $values;

    private $connection;

    public function __construct(Connection $connection, string $tableIdentifier, PreparedParameters $values)
    {
        $this->connection = $connection;
        $this->tableIdentifier = $tableIdentifier;
        $this->values = $values;
    }

    public function execute(): \pulledbits\ActiveRecord\Result
    {
        return $this->connection->execute("INSERT INTO " . $this->tableIdentifier . " (" . join(', ', $this->values->extractColumns()) . ") VALUES (" . join(', ', $this->values->extractParameterizedValues()) . ")", $this->values->extractParameters());
    }
}