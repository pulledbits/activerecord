<?php


namespace pulledbits\ActiveRecord\SQL;


class Connection
{
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $query, array $namedParameters) : \PDOStatement
    {
        $pdostatement = $this->connection->prepare($query);
        $statement = new Statement($pdostatement);
        $statement->addParameters($namedParameters);

        if ($pdostatement->execute() === false) {
            trigger_error("Failed executing query `" . $query . "` (" . json_encode($pdostatement->debugDumpParams()) . "): " . $pdostatement->errorInfo()[2], E_USER_ERROR);
        }

        return $pdostatement;
    }

    public function executeChange(string $query, array $namedParameters) : int {
        $statement = $this->execute($query, $namedParameters);
        return $statement->rowCount();
    }
}