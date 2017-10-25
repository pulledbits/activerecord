<?php


namespace pulledbits\ActiveRecord\SQL;


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

    public function where(PreparedParameters $conditions)
    {
        $this->where = new Where($conditions);
    }

    public function execute(Connection $connection) : Result
    {
        return new Result($connection->execute("SELECT " . join(', ', $this->attributeIdentifiers) . " FROM " . $this->entityTypeIdentifier . $this->where, $this->where->parameters()));
    }
}