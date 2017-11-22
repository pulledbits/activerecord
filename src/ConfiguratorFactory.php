<?php
namespace pulledbits\ActiveRecord;

class ConfiguratorFactory
{
    private $sourceSchema;
    private $path;

    public function __construct(\pulledbits\ActiveRecord\Source\Schema $sourceSchema, string $path)
    {
        $this->sourceSchema = $sourceSchema;
        $this->path = $path;
        if (is_dir($this->path) === false) {
            mkdir($this->path);
        }
    }

    public function generate(RecordFactory $recordFactory, string $entityTypeIdentifier) : RecordConfigurator
    {
        $configuratorPath = $this->path . DIRECTORY_SEPARATOR . $entityTypeIdentifier . '.php';
        if (is_file($configuratorPath) === false) {
            $generator = $this->sourceSchema->describeTable($entityTypeIdentifier);
            $stream = \GuzzleHttp\Psr7\stream_for(fopen($configuratorPath, 'w'));
            $stream->write('<?php namespace pulledbits\\ActiveRecord;');
            $generator->generateConfigurator($stream);
        }
        return require $configuratorPath;
    }
}