<?php
namespace pulledbits\ActiveRecord;
use pulledbits\ActiveRecord\Source\ConfiguratorGeneratorFactory;

return new class {
    public function __construct()
    {
        require getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    public function generatorGeneratorFactory() : ConfiguratorGeneratorFactory {
        return new ConfiguratorGeneratorFactory();
    }
};