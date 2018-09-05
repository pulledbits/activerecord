<?php


namespace pulledbits\ActiveRecord\SQL\MySQL;

class QueryFactory
{
    static function prepareParameters(array $values): Query\PreparedParameters
    {
        return new Query\PreparedParameters($values);
    }

    static function makeWhere(array $conditions): Query\Where
    {
        return new Query\Where(self::prepareParameters($conditions));
    }

    public function makeSelect(string $entityTypeIdentifier, array $attributeIdentifiers): Query
    {
        return new Query\Select($entityTypeIdentifier, $attributeIdentifiers);
    }

    public function makeUpdate(string $tableIdentifier, array $values): Query
    {
        return new Query\Update($tableIdentifier, new Query\Update\Values(self::prepareParameters($values)));
    }

    public function makeInsert(string $tableIdentifier, array $values): Query
    {
        return new Query\Insert($tableIdentifier, self::prepareParameters($values));
    }

    public function makeDelete(string $tableIdentifier): Query
    {
        return new Query\Delete($tableIdentifier);
    }


    public function makeRaw(string $rawSQL) : Query {
        return new Query\Raw($rawSQL);
    }

    public function makeProcedure(string $procedureIdentifier, array $arguments): Query
    {
        return new Query\Procedure($procedureIdentifier, self::prepareParameters($arguments));
    }
}