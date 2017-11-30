<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\SQL\EntityType;

final class Schema implements \pulledbits\ActiveRecord\Source\Schema
{
    private $prototypeTables;
    private $prototypeViews;

    public function __construct(array $prototypeTables, array $prototypeViews)
    {
        $this->prototypeTables = $prototypeTables;
        $this->prototypeViews = $prototypeViews;
    }

    private function entityExists($tableIdentifier) {
        return array_key_exists($tableIdentifier, $this->prototypeTables) || array_key_exists($tableIdentifier, $this->prototypeViews);
    }

    public function describeTable(\pulledbits\ActiveRecord\SQL\Schema $schema, string $tableIdentifier) : RecordConfigurator
    {
        $recordType = new EntityType($schema, $tableIdentifier);

        if (array_key_exists($tableIdentifier, $this->prototypeTables)) {
            return new Record($recordType, $this->prototypeTables[$tableIdentifier]);
        }

        if (array_key_exists($tableIdentifier, $this->prototypeViews)) {
            $underscorePosition = strpos($tableIdentifier, '_');
            if ($underscorePosition < 1) {
                return new Record($recordType, new TableDescription());
            }
            $possibleEntityTypeIdentifier = substr($tableIdentifier, 0, $underscorePosition);
            if ($this->entityExists($possibleEntityTypeIdentifier) === false) {
                return new Record($recordType, new TableDescription());
            }
            return new WrappedEntity($this->describeTable($schema, $possibleEntityTypeIdentifier));
        }
    }
}