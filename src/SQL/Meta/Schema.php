<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\SQL\EntityType;

final class Schema implements \pulledbits\ActiveRecord\Source\Schema
{
    private $schema;
    private $prototypeTables;
    private $prototypeViews;

    public function __construct(\pulledbits\ActiveRecord\SQL\Schema $schema, array $prototypeTables, array $prototypeViews)
    {
        $this->schema = $schema;
        $this->prototypeTables = $prototypeTables;
        $this->prototypeViews = $prototypeViews;
    }

    private function entityExists($tableIdentifier) {
        return array_key_exists($tableIdentifier, $this->prototypeTables) || array_key_exists($tableIdentifier, $this->prototypeViews);
    }

    public function describeTable(string $tableIdentifier) : EntityType
    {
        if (array_key_exists($tableIdentifier, $this->prototypeTables)) {
            return $this->schema->makeRecordType($tableIdentifier, $this->prototypeTables[$tableIdentifier]);
        }

        if (array_key_exists($tableIdentifier, $this->prototypeViews)) {
            $underscorePosition = strpos($tableIdentifier, '_');
            if ($underscorePosition < 1) {
                return $this->schema->makeRecordType($tableIdentifier, new TableDescription());
            }
            $possibleEntityTypeIdentifier = substr($tableIdentifier, 0, $underscorePosition);
            if ($this->entityExists($possibleEntityTypeIdentifier) === false) {
                return $this->schema->makeRecordType($tableIdentifier, new TableDescription());
            }
            return $this->describeTable($possibleEntityTypeIdentifier);
        }
    }
}