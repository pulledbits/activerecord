<?php


namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\SQL\Meta\SchemaFactory;

class Connection
{
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function schema()
    {
        return new Schema(new QueryFactory($this));
    }
    public function sourceSchema()
    {
        return SchemaFactory::makeFromPDO($this, $this->connection);
    }

    public function recordConfigurator(string $entityTypeIdentifier) : RecordConfigurator
    {
        return $this->sourceSchema()->describeTable($entityTypeIdentifier)->generateConfigurator();
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


}