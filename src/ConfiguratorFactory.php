<?php
namespace pulledbits\ActiveRecord;

class ConfiguratorFactory
{
    private $sourceSchema;

    public function __construct(\pulledbits\ActiveRecord\Source\Schema $sourceSchema)
    {
        $this->sourceSchema = $sourceSchema;
    }

    public function generate(RecordFactory $recordFactory, string $entityTypeIdentifier) : RecordConfigurator
    {
        $generator = $this->sourceSchema->describeTable($entityTypeIdentifier);
        $stream = \GuzzleHttp\Psr7\stream_for(fopen('php://memory', 'w'));
        $generator->generateConfigurator($stream);
        $stream->rewind();
        return eval($stream->getContents());
    }
}