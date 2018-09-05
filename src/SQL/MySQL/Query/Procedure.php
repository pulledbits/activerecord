<?php


namespace pulledbits\ActiveRecord\SQL\MySQL\Query;


use pulledbits\ActiveRecord\SQL\Connection;

class Procedure implements \pulledbits\ActiveRecord\SQL\Query
{
    private $procedureIdentifier;
    private $arguments;

    public function __construct(string $procedureIdentifier, PreparedParameters $arguments)
    {
        $this->procedureIdentifier = $procedureIdentifier;
        $this->arguments = $arguments;
    }

    public function execute(Connection $connection) : \pulledbits\ActiveRecord\Result
    {
        return $connection->execute('CALL ' . $this->procedureIdentifier . '(' . join(", ", $this->arguments->extractParameterizedValues()) . ')', $this->arguments->extractParameters());
    }
}