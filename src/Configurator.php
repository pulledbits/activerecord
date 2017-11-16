<?php


namespace pulledbits\ActiveRecord;


class Configurator
{
    private $sourceSchema;
    private $path;
    private $entityTypeIdentifier;

    public function __construct(\pulledbits\ActiveRecord\Source\Schema $sourceSchema, string $entityTypeIdentifier, string $path)
    {
        $this->sourceSchema = $sourceSchema;
        $this->entityTypeIdentifier = $entityTypeIdentifier;
        $this->path = $path;
        if (is_dir($this->path) === false) {
            mkdir($this->path);
        }
    }

    public function generate()
    {
        $configuratorPath = $this->path . DIRECTORY_SEPARATOR . $this->entityTypeIdentifier . '.php';
        if (is_file($configuratorPath) === false) {
            $generatorGeneratorFactory = new \pulledbits\ActiveRecord\Source\GeneratorGeneratorFactory();
            $recordClassDescription = $this->sourceSchema->describeTable(new \pulledbits\ActiveRecord\SQL\Meta\Table(), $this->entityTypeIdentifier);
            $generator = $generatorGeneratorFactory->makeGeneratorGenerator($recordClassDescription);
            file_put_contents($configuratorPath, $generator->generate());
        }
        return require $configuratorPath;
    }
}