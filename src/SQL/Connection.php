<?php


namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\SQL\Query\Result;

class Connection
{
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    static function fromDatabaseURL(string $url) : self
    {
        $parsedUrl = parse_url($url);
        return new \pulledbits\ActiveRecord\SQL\Connection(new \PDO($parsedUrl['scheme'] . ':dbname=' . substr($parsedUrl['path'], 1), $parsedUrl['user'], $parsedUrl['pass'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'')));
    }

    public function schema(\pulledbits\ActiveRecord\RecordFactory $recordFactory)
    {
        return new Schema($recordFactory, new QueryFactory($this));
    }
    public function recordConfigurator($targetDirectory)
    {
        $sourceSchema = \pulledbits\ActiveRecord\Source\SQL\Schema::fromPDO($this->connection);
        return $sourceSchema->recordConfigurator($targetDirectory);
    }

    public function execute(string $query, array $namedParameters) : Result
    {
        $pdostatement = $this->connection->prepare($query);
        $statement = new Statement($pdostatement);
        $statement->addParameters($namedParameters);

        if ($pdostatement->execute() === false) {
            trigger_error("Failed executing query `" . $query . "` (" . json_encode($pdostatement->debugDumpParams()) . "): " . $pdostatement->errorInfo()[2], E_USER_ERROR);
        }

        return new Result($pdostatement);
    }


}