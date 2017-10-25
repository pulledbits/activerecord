<?php


namespace pulledbits\ActiveRecord\SQL;


class Delete
{
    private $tableIdentifier;

    /**
     * @var Where
     */
    private $where;

    public function __construct(string $tableIdentifier)
    {
        $this->tableIdentifier = $tableIdentifier;
    }

    public function where(PreparedParameters $conditions) : void
    {
        $this->where = new Where($conditions);
    }

    public function execute(Connection $connection)
    {
        return $connection->executeChange("DELETE FROM " . $this->tableIdentifier . $this->where, $this->where->parameters());
    }
}