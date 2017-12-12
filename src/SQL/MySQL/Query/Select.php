<?php


namespace pulledbits\ActiveRecord\SQL\MySQL\Query;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\SQL\Connection;
use pulledbits\ActiveRecord\SQL\MySQL\QueryFactory;

class Select
{
    private $entityTypeIdentifier;
    private $attributeIdentifiers;

    /**
     * @var Where
     */
    private $where;

    public function __construct($entityTypeIdentifier, array $attributeIdentifiers)
    {
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

    public function execute(Connection $connection): Result
    {
        return $connection->execute("SELECT " . join(', ', $this->attributeIdentifiers) . " FROM " . $this->entityTypeIdentifier . $this->where, $this->where->parameters());
    }
}