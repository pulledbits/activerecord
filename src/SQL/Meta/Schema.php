<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\ConfiguratorFactory;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;
use pulledbits\ActiveRecord\Source\TableDescription;

final class Schema implements \pulledbits\ActiveRecord\Source\Schema
{
    private $recordConfiguratorGenerators;

    public function __construct(array $prototypeTables, array $prototypeViews)
    {
        $this->recordConfiguratorGenerators = [];
        foreach ($prototypeTables as $prototypeTableIdentifier => $prototypeTable) {
            $this->recordConfiguratorGenerators[$prototypeTableIdentifier] = new RecordConfiguratorGenerator\Record($prototypeTable);
        }

        foreach ($prototypeViews as $viewIdentifier => $viewSQL) {
            $this->recordConfiguratorGenerators[$viewIdentifier] = new RecordConfiguratorGenerator\Record(new TableDescription());

            $underscorePosition = strpos($viewIdentifier, '_');
            if ($underscorePosition < 1) {
                continue;
            }

            $possibleEntityTypeIdentifier = substr($viewIdentifier, 0, $underscorePosition);
            if (array_key_exists($possibleEntityTypeIdentifier, $this->recordConfiguratorGenerators) === false) {
                continue;
            }

            $this->recordConfiguratorGenerators[$viewIdentifier] = new RecordConfiguratorGenerator\WrappedEntity($possibleEntityTypeIdentifier);
        }
    }

    public function createConfigurator()
    {
        return new ConfiguratorFactory($this);
    }

    public function describeTable(string $tableIdentifier) : RecordConfiguratorGenerator
    {
        return $this->recordConfiguratorGenerators[$tableIdentifier];
    }
}