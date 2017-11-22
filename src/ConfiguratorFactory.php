<?php
namespace pulledbits\ActiveRecord;

class ConfiguratorFactory
{
    private $generatorGeneratorFactory;
    private $path;

    public function __construct(\pulledbits\ActiveRecord\Source\RecordConfiguratorGeneratorFactory $generatorGeneratorFactory, string $path)
    {
        $this->generatorGeneratorFactory = $generatorGeneratorFactory;
        $this->path = $path;
        if (is_dir($this->path) === false) {
            mkdir($this->path);
        }
    }

    public function generate(RecordFactory $recordFactory, string $entityTypeIdentifier) : RecordConfigurator
    {
        $configuratorPath = $this->path . DIRECTORY_SEPARATOR . $entityTypeIdentifier . '.php';
        if (is_file($configuratorPath) === false) {
            $generator = $this->generatorGeneratorFactory->makeConfiguratorGenerator($entityTypeIdentifier);
            $generator->generateConfigurator(\GuzzleHttp\Psr7\stream_for(fopen($configuratorPath, 'w')));
        }
        return require $configuratorPath;
    }
}