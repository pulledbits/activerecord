<?php


namespace pulledbits\ActiveRecord\SQL\MySQL\Query;


use pulledbits\ActiveRecord\SQL\Connection;

class Procedure implements \pulledbits\ActiveRecord\SQL\Query
{
    private $procedureIdentifier;
    private $arguments;
    private $connection;

    public function __construct(Connection $connection, string $procedureIdentifier, PreparedParameters $arguments)
    {
        $this->connection = $connection;
        $this->procedureIdentifier = $procedureIdentifier;
        $this->arguments = $arguments;
    }

    public function execute() : \pulledbits\ActiveRecord\Result
    {
        return $this->connection->execute('CALL ' . $this->procedureIdentifier . '(' . join(", ", $this->arguments->extractParameterizedValues()) . ')', $this->arguments->extractParameters());
    }
}