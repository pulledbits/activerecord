<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 1-12-16
 * Time: 13:32
 */

namespace ActiveRecord;


class Table
{
    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @var string
     */
    private $identifier;

    public function __construct(\PDO $connection, string $identifier) {
        $this->connection = $connection;
        $this->identifier = $identifier;
    }

    public function select(string $fields) {
        $statement = $this->connection->prepare('SELECT ' . $fields . ' FROM ' . $this->identifier);
        return $statement->fetchAll();
    }
}