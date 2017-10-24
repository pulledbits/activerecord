<?php


namespace pulledbits\ActiveRecord\SQL;


class PDO
{
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
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