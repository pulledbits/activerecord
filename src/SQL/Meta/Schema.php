<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\Record;
use pulledbits\ActiveRecord\SQL\Connection;

final class Schema implements \pulledbits\ActiveRecord\Source\Schema
{
    private $schema;
    private $prototypeEntities;

    public function __construct(Connection $connection, \pulledbits\ActiveRecord\Schema $schema)
    {
        $this->schema = $schema;
        $this->prototypeEntities = $schema->listEntityTypes();
    }

    public function makeRecord(string $tableIdentifier) : Record
    {
        return $this->schema->makeRecord($tableIdentifier, $this->prototypeEntities[$tableIdentifier]);
    }
}