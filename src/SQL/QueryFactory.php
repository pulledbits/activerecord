<?php


namespace pulledbits\ActiveRecord\SQL;


class QueryFactory
{

    public function makeSelect(string $entityTypeIdentifier, array $attributeIdentifiers)
    {
        return new Query\Select($entityTypeIdentifier, $attributeIdentifiers);
    }

    public function makeUpdate(string $tableIdentifier, Query\Update\Values $values)
    {
        return new Query\Update($tableIdentifier, $values);
    }

    public function makeInsert(string $tableIdentifier, Query\PreparedParameters $preparedParameters)
    {
        return new Query\Insert($tableIdentifier, $preparedParameters);
    }

    public function makeDelete(string $tableIdentifier)
    {
        return new Query\Delete($tableIdentifier);
    }

    public function makeProcedure(string $procedureIdentifier, Query\PreparedParameters $preparedParameters)
    {
        return new Query\Procedure($procedureIdentifier, $preparedParameters);
    }
}