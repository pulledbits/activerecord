<?php


namespace pulledbits\ActiveRecord\SQL\MySQL;

class QueryFactory
{
    public function makeSelect(string $entityTypeIdentifier, array $attributeIdentifiers): Query\Select
    {
        return new Query\Select($entityTypeIdentifier, $attributeIdentifiers);
    }

    public function makeUpdate(string $tableIdentifier, array $values): Query\Update
    {
        return new Query\Update($tableIdentifier, new Query\Update\Values($this->prepareParameters($values)));
    }

    public function prepareParameters(array $values): Query\PreparedParameters
    {
        return new Query\PreparedParameters($values);
    }

    public function makeInsert(string $tableIdentifier, array $values): Query\Insert
    {
        return new Query\Insert($tableIdentifier, $this->prepareParameters($values));
    }

    public function makeDelete(string $tableIdentifier): Query\Delete
    {
        return new Query\Delete($tableIdentifier);
    }

    public function makeProcedure(string $procedureIdentifier, array $arguments): Query\Procedure
    {
        return new Query\Procedure($procedureIdentifier, $this->prepareParameters($arguments));
    }

    public function makeWhere($conditions): Query\Where
    {
        return new Query\Where($this->prepareParameters($conditions));
    }
}