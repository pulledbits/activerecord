<?php


namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\ConfiguratorFactory;
use pulledbits\ActiveRecord\RecordConfigurator;

class Connection
{
    private $connection;
    private $configurator;

    public function __construct(\PDO $connection, ConfiguratorFactory $configurator)
    {
        $this->connection = $connection;
        $this->configurator = $configurator;
    }

    public function schema()
    {
        return new Schema(new QueryFactory($this));
    }
    public function recordConfigurator(string $entityTypeIdentifier) : RecordConfigurator
    {
        return $this->configurator->generate(new EntityFactory($this->schema(), $entityTypeIdentifier), $entityTypeIdentifier);
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