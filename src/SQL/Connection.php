<?php


namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\SQL\Meta\SchemaFactory;
use pulledbits\ActiveRecord\SQL\Query\Result;

class Connection
{
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function schema(string $schemaIdentifier)
    {
        $schema = new Schema($this, new QueryFactory($schemaIdentifier), $schemaIdentifier);
        return $schema;
    }

    public function execute(string $query, array $namedParameters) : Result
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