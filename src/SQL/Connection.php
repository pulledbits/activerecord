<?php


namespace pulledbits\ActiveRecord\SQL;


class Connection
{
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    static function fromDatabaseURL(string $databaseURL) : self
    {
        $url = parse_url($databaseURL);
        $connection = new \PDO($url['scheme'] . ':dbname=' . substr($url['path'], 1), $url['user'], $url['pass'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
        return new self($connection);
    }

    public function execute(string $query, array $namedParameters) : \PDOStatement
    {
        $statement = $this->connection->prepare($query);
        foreach ($namedParameters as $namedParameter => $value) {
            $statement->bindValue($namedParameter, $value, \PDO::PARAM_STR);
        }

        if ($statement->execute() === false) {
            trigger_error("Failed executing query `" . $query . "` (" . json_encode($namedParameters) . "): " . $statement->errorInfo()[2], E_USER_ERROR);
        }

        return $statement;
    }
}