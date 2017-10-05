<?php
namespace pulledbits\ActiveRecord;
use pulledbits\ActiveRecord\Source\GeneratorGeneratorFactory;

return new class {
    public function __construct()
    {
        require getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    public function generatorGeneratorFactory() : GeneratorGeneratorFactory {
        return new GeneratorGeneratorFactory();
    }
};