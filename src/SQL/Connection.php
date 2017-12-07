<?php


namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\SQL\MySQL\Query\Result;
use pulledbits\ActiveRecord\SQL\MySQL\QueryFactory;
use pulledbits\ActiveRecord\SQL\MySQL\Schema;

class Connection
{
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function schema(string $schemaIdentifier)
    {
        return new Schema($this, new QueryFactory($schemaIdentifier), $schemaIdentifier);
    }

    public function execute(string $query, array $namedParameters): Result
    {
        $pdostatement = $this->connection->prepare($query);
        $statement = new Statement($pdostatement);
        $statement->addParameters($namedParameters);

        if ($pdostatement->execute() === false) {
            trigger_error("Failed executing query `" . $query . "` (" . json_encode($pdostatement->debugDumpParams()) . "): " . $pdostatement->errorInfo()[2], E_USER_ERROR);
        }

        return new Result($statement);
    }


}