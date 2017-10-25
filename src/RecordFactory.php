<?php
namespace pulledbits\ActiveRecord;

final class RecordFactory {

    private $sourceSchema;
    private $path;

    public function __construct(Source\Schema $sourceSchema, string $path)
    {
        $this->sourceSchema = $sourceSchema;
        $this->path = $path;
        if (is_dir($this->path) === false) {
            mkdir($this->path);
        }
    }

    private function generateConfigurator(string $entityTypeIdentifier)
    {
        $configuratorPath = $this->path . DIRECTORY_SEPARATOR . $entityTypeIdentifier . '.php';
        if (is_file($configuratorPath) === false) {
            $generatorGeneratorFactory = new Source\GeneratorGeneratorFactory();
            $recordClassDescription = $this->sourceSchema->describeTable(new SQL\Meta\Table(), $entityTypeIdentifier);
            $generator = $generatorGeneratorFactory->makeGeneratorGenerator($recordClassDescription);
            file_put_contents($configuratorPath, $generator->generate());
        }
        return require $configuratorPath;
    }

    public function makeRecord(Schema $schema, string $entityTypeIdentifier) : Entity
    {

        $configurator = $this->generateConfigurator($entityTypeIdentifier);
        return $configurator($schema, $entityTypeIdentifier);
    }
}