<?php
namespace pulledbits\ActiveRecord\Source;


use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator\Record;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator\WrappedEntity;

final class RecordConfiguratorGeneratorFactory
{
    private $sourceSchema;

    public function __construct(Schema $sourceSchema)
    {
        $this->sourceSchema = $sourceSchema;
    }

    public function makeConfiguratorGenerator(string $entityTypeIdentifier) : RecordConfiguratorGenerator
    {
        $entityDescription = $this->sourceSchema->describeTable($entityTypeIdentifier);
        if (array_key_exists('entityTypeIdentifier', $entityDescription)) {
            return new WrappedEntity($entityDescription['entityTypeIdentifier']);
        }
        $entityGeneratorGenerator = new Record($entityDescription);

        return $entityGeneratorGenerator;
    }

}