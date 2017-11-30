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

    public function describeTable(\pulledbits\ActiveRecord\SQL\Schema $schema, string $tableIdentifier) : RecordConfigurator
    {
        $this->recordConfiguratorGenerators = [];
        foreach ($this->prototypeTables as $prototypeTableIdentifier => $prototypeTable) {
            $recordType = new EntityType($schema, $prototypeTableIdentifier);
            $this->recordConfiguratorGenerators[$prototypeTableIdentifier] = new Record($recordType, $prototypeTable);
        }

        foreach ($this->prototypeViews as $viewIdentifier => $viewSQL) {
            $recordType = new EntityType($schema, $viewIdentifier);
            $this->recordConfiguratorGenerators[$viewIdentifier] = new Record($recordType, new TableDescription());

            $underscorePosition = strpos($viewIdentifier, '_');
            if ($underscorePosition < 1) {
                continue;
            }

            $possibleEntityTypeIdentifier = substr($viewIdentifier, 0, $underscorePosition);
            if (array_key_exists($possibleEntityTypeIdentifier, $this->recordConfiguratorGenerators) === false) {
                continue;
            }

            $this->recordConfiguratorGenerators[$viewIdentifier] = new WrappedEntity($this->recordConfiguratorGenerators[$possibleEntityTypeIdentifier]);
        }
        return $this->recordConfiguratorGenerators[$tableIdentifier];
    }
}