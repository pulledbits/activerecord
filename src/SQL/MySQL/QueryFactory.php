<?php


namespace pulledbits\ActiveRecord\SQL\MySQL;


use pulledbits\ActiveRecord\SQL\Connection;

class QueryFactory
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    static function prepareParameters(array $values): Query\PreparedParameters
    {
        return new Query\PreparedParameters($values);
    }

    static function makeWhere(array $conditions): Query\Where
    {
        return new Query\Where(self::prepareParameters($conditions));
    }

    public function makeSelect(string $entityTypeIdentifier, array $attributeIdentifiers): \pulledbits\ActiveRecord\SQL\Query
    {
        return new Query\Select($this->connection, $entityTypeIdentifier, $attributeIdentifiers);
    }

    public function makeUpdate(string $tableIdentifier, array $values): \pulledbits\ActiveRecord\SQL\Query
    {
        return new Query\Update($this->connection, $tableIdentifier, new Query\Update\Values(self::prepareParameters($values)));
    }

    public function makeInsert(string $tableIdentifier, array $values): \pulledbits\ActiveRecord\SQL\Query
    {
        return new Query\Insert($this->connection, $tableIdentifier, self::prepareParameters($values));
    }

    public function makeDelete(string $tableIdentifier): \pulledbits\ActiveRecord\SQL\Query
    {
        return new Query\Delete($this->connection, $tableIdentifier);
    }


    public function makeRaw(string $rawSQL) : \pulledbits\ActiveRecord\SQL\Query {
        return new Query\Raw($this->connection, $rawSQL);
    }

    public function makeProcedure(string $procedureIdentifier, array $arguments): \pulledbits\ActiveRecord\SQL\Query
    {
        return new Query\Procedure($this->connection, $procedureIdentifier, self::prepareParameters($arguments));
    }
}