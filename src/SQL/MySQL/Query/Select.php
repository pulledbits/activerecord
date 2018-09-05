<?php


namespace pulledbits\ActiveRecord\SQL\MySQL\Query;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\SQL\Connection;
use pulledbits\ActiveRecord\SQL\MySQL\QueryFactory;

class Select implements \pulledbits\ActiveRecord\SQL\Query
{
    private $entityTypeIdentifier;
    private $attributeIdentifiers;

    /**
     * @var Where
     */
    private $where;
    private $connection;

    public function __construct(Connection $connection, $entityTypeIdentifier, array $attributeIdentifiers)
    {
        $this->connection = $connection;

        if (count($attributeIdentifiers) === 0) {
            $attributeIdentifiers[] = '*';
        }
        $this->entityTypeIdentifier = $entityTypeIdentifier;
        $this->attributeIdentifiers = $attributeIdentifiers;
    }

    public function where(array $where)
    {
        $this->where = QueryFactory::makeWhere($where);
    }

    public function execute(): \pulledbits\ActiveRecord\Result
    {
        return $this->connection->execute("SELECT " . join(', ', $this->attributeIdentifiers) . " FROM " . $this->entityTypeIdentifier . $this->where, $this->where->parameters());
    }
}