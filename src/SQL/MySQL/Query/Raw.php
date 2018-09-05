<?php

namespace pulledbits\ActiveRecord\SQL\MySQL\Query;

use pulledbits\ActiveRecord\SQL\Connection;

class Raw implements \pulledbits\ActiveRecord\SQL\Query
{
    /**
     * @var string
     */
    private $SQL;

    public function __construct(string $SQL) {
        $this->SQL = $SQL;
    }

    public function execute(Connection $connection) : \pulledbits\ActiveRecord\Result
    {
        return $connection->execute($this->SQL, []);
    }
}