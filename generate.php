<?php

use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;

$applicationBootstrap = require __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
$applicationBootstrap();

if ($_SERVER['argc'] < 3) {
    exit('please enter destination namespace and path');
}

$targetNamespace = $_SERVER['argv'][1] . '\\Record';

$targetDirectory = $_SERVER['argv'][2];
$recordsDirectory = $targetDirectory . DIRECTORY_SEPARATOR . 'Record';
if (file_exists($recordsDirectory) == false) {
    mkdir($recordsDirectory);
}

if ($_SERVER['argc'] === 4) {
    $dburl = $_SERVER['argv'][3];
} else {
    $dbhost = readline('Please enter database hostname: ');
    $dbname = readline('Please enter database name on ' . $dbhost . ': ');
    $dbuser = readline('Please enter username for ' . $dbname . ': ');
    $dbpass = readline('Please enter password for ' . $dbuser . '@' . $dbhost . '/' . $dbname . ': ');
    $dburl = 'mysql://' . $dbuser . ':' . $dbpass . '@' . $dbhost . '/' . $dbname;
}

$config = new \Doctrine\DBAL\Configuration();
$connectionParams = array(
    'url' => $dburl
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$generator = new CodeGenerator();

$sourceSchema = new \ActiveRecord\Source\Schema($conn->getSchemaManager());
$sourceSchema->describe(new \ActiveRecord\Source\Table($targetNamespace), function(string $tableName, array $recordClassDescription) use ($generator, $recordsDirectory) {
    $recordClass = new gossi\codegen\model\PhpClass($recordClassDescription['identifier']);
    $recordClass->setFinal(true);

    foreach ($recordClassDescription['methods'] as $methodIdentifier => $methodDescription) {
        $method = PhpMethod::create($methodIdentifier);
        if ($methodDescription['static']) {
            $method->setStatic(true);
        }
        foreach ($methodDescription['parameters'] as $methodParameterIdentifier => $methodParameterType) {
            $parameter = PhpParameter::create($methodParameterIdentifier);
            $parameter->setType($methodParameterType);

            $method->addParameter($parameter);
        }
        $method->setBody(join(PHP_EOL, $methodDescription['body']));
        $recordClass->setMethod($method);
    }
    $classFilename = $tableName . '.class.php';
    file_put_contents($recordsDirectory . DIRECTORY_SEPARATOR . $classFilename, '<?php' . PHP_EOL . $generator->generate($recordClass));
    file_put_contents($recordsDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php
namespace ' . $recordClass->getNamespace() . ';
require_once __DIR__ . DIRECTORY_SEPARATOR . \'' . $classFilename . '\';
return function(\ActiveRecord\Schema\Asset $asset, array $values) {
    return new class($asset, $values) implements \ActiveRecord\Record {
    
        /**
         * @var \ActiveRecord\Asset
         */
        private $asset = NULL;
    
        /**
         * @var array
         */
        private $values = NULL;
    
        /**
         * @param \ActiveRecord\Asset $asset
         * @param array $values
         */
         
        /**
         * @var \\' . $recordClass->getQualifiedName() . '
         */
        private $metaRecord;
        
        public function __construct(\ActiveRecord\Schema\Asset $asset, array $values) {
            $this->asset = $asset;
            $this->values = $values;
            
            $this->metaRecord = new ' . $recordClass->getName() . '($asset, $values);
        }
        
        /**
         * @param string $property
         */
        public function __get($property) {
            return $this->values[$property];
        }

        /**
         * @param string $property
         * @param string $value
         */
        public function __set($property, $value) {
            if (count($this->asset->update([$property => $this->values[$property]], $this->primaryKey())) > 0) {
                $this->values[$property] = $value;
            }
        }

        public function primaryKey() {
            return array_slice_key($this->values, $this->metaRecord->identifier());
        }
    
        /**
         */
        public function delete() {
            return $this->asset->delete($this->primaryKey());
        }
        
        public function __call(string $method, array $arguments) {
            if (substr($method, 0, 6) === \'fetchBy\') {
                $reference = $this->metaRecord->references()[substr($method, 6)];
                $fkColumns = array_keys($reference[\'where\']);
                $fkLocalColumns = array_values($reference[\'where\']);
                return $this->asset->selectFrom($reference[\'table\'], $fkColumns, array_combine($fkColumns, array_slice_key($this->values, $fkLocalColumns)));
            }
            return call_user_func_array([$this->metaRecord, $method], $arguments);
        }
    };
};');
});

file_put_contents($targetDirectory . DIRECTORY_SEPARATOR . 'factory.php', '<?php return new \ActiveRecord\RecordFactory(__DIR__ . DIRECTORY_SEPARATOR . \'Record\');');

echo 'Done' . PHP_EOL;