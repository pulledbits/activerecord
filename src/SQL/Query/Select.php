<?php


namespace pulledbits\ActiveRecord\SQL\Query;


use pulledbits\ActiveRecord\SQL\Connection;

class Select
{
    private $connection;
    private $entityTypeIdentifier;
    private $attributeIdentifiers;

    /**
     * @var Where
     */
    private $where;

    public function __construct(Connection $connection, $entityTypeIdentifier, array $attributeIdentifiers)
    {
        if (count($attributeIdentifiers) === 0) {
            $attributeIdentifiers[] = '*';
        }
        $this->connection = $connection;
        $this->entityTypeIdentifier = $entityTypeIdentifier;
        $this->attributeIdentifiers = $attributeIdentifiers;
    }

    public function where(Where $where)
    {
        $this->where = $where;
    }

    public function execute() : Result
    {
        return $this->connection->execute("SELECT " . join(', ', $this->attributeIdentifiers) . " FROM " . $this->entityTypeIdentifier . $this->where, $this->where->parameters());
    }
}