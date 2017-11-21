<?php
namespace pulledbits\ActiveRecord;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGeneratorFactory;

return new class {
    public function __construct()
    {
        require getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    public function generatorGeneratorFactory() : RecordConfiguratorGeneratorFactory {
        return new RecordConfiguratorGeneratorFactory();
    }
};