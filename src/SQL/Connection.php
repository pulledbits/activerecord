<?php


namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\RecordFactory;
use pulledbits\ActiveRecord\SQL\Query\Result;

class Connection
{
    private $connection;
    private $targetDirectory;

    public function __construct(\PDO $connection, string $targetDirectory)
    {
        $this->connection = $connection;
        $this->targetDirectory = $targetDirectory;
    }

    static function fromDatabaseURL(string $url, string $targetDirectory) : self
    {
        $parsedUrl = parse_url($url);
        return new self(new \PDO($parsedUrl['scheme'] . ':dbname=' . substr($parsedUrl['path'], 1), $parsedUrl['user'], $parsedUrl['pass'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'')), $targetDirectory);
    }

    public function schema()
    {
        return new Schema(new QueryFactory($this));
    }
    public function recordConfigurator()
    {
        $sourceSchema = Meta\Schema::fromPDO($this->connection);
        return new RecordFactory($sourceSchema, $this->targetDirectory);
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