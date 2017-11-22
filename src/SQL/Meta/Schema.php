<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\ConfiguratorFactory;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

final class Schema implements \pulledbits\ActiveRecord\Source\Schema
{
    private $cachedTableDescriptions;

    public function __construct(array $prototypeTables, array $dbalViews)
    {
        $this->cachedTableDescriptions = [];
        foreach ($prototypeTables as $prototypeTableIdentifier => $prototypeTable) {
            $this->cachedTableDescriptions[$prototypeTableIdentifier] = new RecordConfiguratorGenerator\Record($prototypeTable);
        }

        foreach ($dbalViews as $viewIdentifier => $viewSQL) {
            $this->cachedTableDescriptions[$viewIdentifier] = new RecordConfiguratorGenerator\Record(['identifier' => [], 'requiredAttributeIdentifiers' => [], 'references' => []]);

            $underscorePosition = strpos($viewIdentifier, '_');
            if ($underscorePosition < 1) {
                continue;
            }

            $possibleEntityTypeIdentifier = substr($viewIdentifier, 0, $underscorePosition);
            if (array_key_exists($possibleEntityTypeIdentifier, $this->cachedTableDescriptions) === false) {
                continue;
            }

            $this->cachedTableDescriptions[$viewIdentifier] = new RecordConfiguratorGenerator\WrappedEntity($possibleEntityTypeIdentifier);
        }
    }

    public function createConfigurator(string $targetDirectory)
    {
        return new ConfiguratorFactory($this, $targetDirectory);
    }

    public function describeTable(string $tableIdentifier) : RecordConfiguratorGenerator
    {
        return $this->cachedTableDescriptions[$tableIdentifier];
    }
}