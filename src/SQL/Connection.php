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
        $this->schema = new Schema($this, new QueryFactory());
        $this->sourceSchema = new \pulledbits\ActiveRecord\SQL\Meta\Schema($this, $this->schema);
    }

    public function schema()
    {
        return $this->schema;
    }

    public function execute(string $query, array $namedParameters) : Statement
    {
        $pdostatement = $this->connection->prepare($query);
        $statement = new Statement($pdostatement);
        $statement->addParameters($namedParameters);

        if ($pdostatement->execute() === false) {
            trigger_error("Failed executing query `" . $query . "` (" . json_encode($pdostatement->debugDumpParams()) . "): " . $pdostatement->errorInfo()[2], E_USER_ERROR);
        }

        return $statement;
    }

    public function query(string $entityTypeIdentifier, string $query, array $namedParameters) : Result {
        $statement = $this->execute($query, $namedParameters);
        return new Result($statement, $this->sourceSchema->describeTable($entityTypeIdentifier));
    }


}