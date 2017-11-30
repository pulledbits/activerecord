<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\SQL\EntityType;

final class Schema implements \pulledbits\ActiveRecord\Source\Schema
{
    private $schema;
    private $prototypeEntities;

    public function __construct(\pulledbits\ActiveRecord\Schema $schema, array $prototypeEntities)
    {
        $this->schema = $schema;
        $this->prototypeEntities = $prototypeEntities;
    }

    public function describeTable(string $tableIdentifier) : EntityType
    {
        if (array_key_exists($tableIdentifier, $this->prototypeEntities)) {
            return $this->schema->makeRecordType($tableIdentifier, $this->prototypeEntities[$tableIdentifier]);
        }
    }
}