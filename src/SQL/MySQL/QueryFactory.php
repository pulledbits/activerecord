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

    public function makeSelect(string $entityTypeIdentifier, array $attributeIdentifiers): Query\Select
    {
        return new Query\Select($entityTypeIdentifier, $attributeIdentifiers);
    }

    public function makeUpdate(string $tableIdentifier, array $values): Query\Update
    {
        return new Query\Update($tableIdentifier, new Query\Update\Values(self::prepareParameters($values)));
    }

    public function makeInsert(string $tableIdentifier, array $values): Query\Insert
    {
        return new Query\Insert($tableIdentifier, self::prepareParameters($values));
    }

    public function makeDelete(string $tableIdentifier): Query\Delete
    {
        return new Query\Delete($tableIdentifier);
    }

    public function makeProcedure(string $procedureIdentifier, array $arguments): Query\Procedure
    {
        return new Query\Procedure($procedureIdentifier, self::prepareParameters($arguments));
    }
}