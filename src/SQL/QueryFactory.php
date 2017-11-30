<?php


namespace pulledbits\ActiveRecord\SQL;


use pulledbits\ActiveRecord\SQL\Query\PreparedParameters;

class QueryFactory
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function makeSelect(string $entityTypeIdentifier, array $attributeIdentifiers) : Query\Select
    {
        return new Query\Select($this->connection, $entityTypeIdentifier, $attributeIdentifiers);
    }

    public function makeUpdate(string $tableIdentifier, array $values) : Query\Update
    {
        return new Query\Update($this->connection, $tableIdentifier, new Query\Update\Values($this->prepareParameters($values)));
    }

    public function makeInsert(string $tableIdentifier, array $values) : Query\Insert
    {
        return new Query\Insert($this->connection, $tableIdentifier, $this->prepareParameters($values));
    }

    public function makeDelete(string $tableIdentifier) : Query\Delete
    {
        return new Query\Delete($this->connection, $tableIdentifier);
    }

    public function makeProcedure(string $procedureIdentifier, array $arguments) : Query\Procedure
    {
        return new Query\Procedure($this->connection, $procedureIdentifier, $this->prepareParameters($arguments));
    }

    public function prepareParameters(array $values) : PreparedParameters
    {
        return new Query\PreparedParameters($values);
    }

    public function makeWhere($conditions) : Query\Where
    {
        return new Query\Where($this->prepareParameters($conditions));
    }
}