<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\SQL\Connection;
use pulledbits\ActiveRecord\SQL\EntityType;

final class Schema implements \pulledbits\ActiveRecord\Source\Schema
{
    private $recordConfiguratorGenerators;

    public function __construct(Connection $connection, array $prototypeTables, array $prototypeViews)
    {
        $this->connection = $connection;

        $schema = $this->connection->schema();


        $this->recordConfiguratorGenerators = [];
        foreach ($prototypeTables as $prototypeTableIdentifier => $prototypeTable) {
            $recordType = new EntityType($schema, $prototypeTableIdentifier);
            $this->recordConfiguratorGenerators[$prototypeTableIdentifier] = new Record($recordType, $prototypeTable);
        }

        foreach ($prototypeViews as $viewIdentifier => $viewSQL) {
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
    }

    public function describeTable(string $tableIdentifier) : RecordConfigurator
    {
        return $this->recordConfiguratorGenerators[$tableIdentifier];
    }
}