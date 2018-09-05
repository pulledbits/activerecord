<?php

namespace pulledbits\ActiveRecord\SQL\MySQL\Query;

use pulledbits\ActiveRecord\SQL\Connection;

class Raw implements \pulledbits\ActiveRecord\SQL\Query
{
    /**
     * @var string
     */
    private $SQL;
    private $connection;

    public function __construct(Connection $connection, string $SQL)
    {
        $this->connection = $connection;
        $this->SQL = $SQL;
    }

    public function execute() : \pulledbits\ActiveRecord\Result
    {
        return $this->connection->execute($this->SQL, []);
    }
}