<?php
namespace pulledbits\ActiveRecord;
use pulledbits\ActiveRecord\Source\GeneratorGeneratorFactory;
use pulledbits\ActiveRecord\SQL\Source\Schema;

return new class {
    public function __construct()
    {
        require getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    public function sourceSchema(string $dburl) : Schema {
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = [
            'url' => $dburl
        ];
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        return new \pulledbits\ActiveRecord\SQL\Source\Schema($conn->getSchemaManager());
    }

    public function generatorGeneratorFactory() : GeneratorGeneratorFactory {
        return new GeneratorGeneratorFactory();
    }
};