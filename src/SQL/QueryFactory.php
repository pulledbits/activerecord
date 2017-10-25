<?php


namespace pulledbits\ActiveRecord\SQL;


class QueryFactory
{

    public function makeSelect(string $entityTypeIdentifier, array $attributeIdentifiers) : Query\Select
    {
        return new Query\Select($entityTypeIdentifier, $attributeIdentifiers);
    }

    public function makeUpdate(string $tableIdentifier, array $values) : Query\Update
    {
        return new Query\Update($tableIdentifier, new Query\Update\Values(new Query\PreparedParameters($values)));
    }

    public function makeInsert(string $tableIdentifier, array $values) : Query\Insert
    {
        return new Query\Insert($tableIdentifier, new Query\PreparedParameters($values));
    }

    public function makeDelete(string $tableIdentifier) : Query\Delete
    {
        return new Query\Delete($tableIdentifier);
    }

    public function makeProcedure(string $procedureIdentifier, Query\PreparedParameters $preparedParameters) : Query\Procedure
    {
        return new Query\Procedure($procedureIdentifier, $preparedParameters);
    }

    public function makeWhere($conditions) : Query\Where
    {
        return new Query\Where(new Query\PreparedParameters($conditions));
    }
}