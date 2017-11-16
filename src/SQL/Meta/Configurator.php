<?php
namespace pulledbits\ActiveRecord\SQL\Meta;


class Configurator
{
    private $generatorGeneratorFactory;
    private $path;

    public function __construct(\pulledbits\ActiveRecord\Source\GeneratorGeneratorFactory $generatorGeneratorFactory, string $path)
    {
        $this->generatorGeneratorFactory = $generatorGeneratorFactory;
        $this->path = $path;
        if (is_dir($this->path) === false) {
            mkdir($this->path);
        }
    }

    public function generate(string $entityTypeIdentifier)
    {
        $configuratorPath = $this->path . DIRECTORY_SEPARATOR . $entityTypeIdentifier . '.php';
        if (is_file($configuratorPath) === false) {
            $generator = $this->generatorGeneratorFactory->makeGeneratorGenerator($entityTypeIdentifier);
            file_put_contents($configuratorPath, $generator->generate());
        }
        return require $configuratorPath;
    }
}