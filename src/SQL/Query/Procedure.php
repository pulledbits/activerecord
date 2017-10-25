<?php


namespace pulledbits\ActiveRecord\SQL\Query;


use pulledbits\ActiveRecord\SQL\Connection;

class Procedure
{
    private $connection;
    private $procedureIdentifier;
    private $arguments;

    public function __construct(Connection $connection, string $procedureIdentifier, PreparedParameters $arguments)
    {
        $this->connection = $connection;
        $this->procedureIdentifier = $procedureIdentifier;
        $this->arguments = $arguments;
    }

    public function execute()
    {
        $this->connection->execute('CALL ' . $this->procedureIdentifier . '(' . join(", ", $this->arguments->extractParameterizedValues()) . ')', $this->arguments->extractParameters());
    }
}