<?php

use gossi\codegen\model\PhpProperty;
use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;

require __DIR__ . '/vendor/autoload.php';

if ($_SERVER['argc'] < 3) {
    exit('please enter destination namespace and path');
}

$targetNamespace = $_SERVER['argv'][1];

$targetDirectory = $_SERVER['argv'][2];
$recordsDirectory = $targetDirectory . DIRECTORY_SEPARATOR . 'Record';
if (file_exists($recordsDirectory) == false) {
    mkdir($recordsDirectory);
}

$config = new \Doctrine\DBAL\Configuration();
//..
$connectionParams = array(
    'url' => 'mysql://teach:teach@localhost/teach'
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$generator = new CodeGenerator();

function createMethod(string $identifier, array $parameters, array $body) {
    $method = PhpMethod::create($identifier);
    foreach ($parameters as $methodParameterIdentifier => $methodParameterType) {
        $byReference = false;
        if (substr($methodParameterIdentifier,0,1) === '&') {
            $byReference = true;
            $methodParameterIdentifier = substr($methodParameterIdentifier,1);
        }
        $parameter = PhpParameter::create($methodParameterIdentifier);
        $parameter->setType($methodParameterType);
        $parameter->setPassedByReference($byReference);
        $method->addParameter($parameter);
    }
    $method->setBody(join(PHP_EOL, $body));
    return $method;
}

function createPHPFile(string $filename, string $code) {
    file_put_contents($filename, '<?php' . PHP_EOL . $code);
}


$sourceSchema = new \ActiveRecord\Source\Schema($conn->getSchemaManager());

$schemaDescription = $sourceSchema->describe($targetNamespace);

foreach ($schemaDescription['recordClasses'] as $tableName => $recordClassDescription) {
    $recordClass = new gossi\codegen\model\PhpClass($recordClassDescription['identifier']);
    $recordClass->setFinal(true);

    foreach ($recordClassDescription['properties'] as $propertyIdentifier => $propertyType) {
        $recordClass->setProperty(PhpProperty::create($propertyIdentifier)->setType($propertyType)->setVisibility('private'));
    }

    $recordClass->setProperty(PhpProperty::create("schema")->setType('\ActiveRecord\Schema')->setVisibility('private'));
    $recordClass->setMethod(createMethod("__construct", ["schema" => '\ActiveRecord\Schema'], ['$this->schema = $schema;']));

    foreach ($recordClassDescription['methods'] as $methodIdentifier => $methodDescription) {
        $recordClass->setMethod(createMethod($methodIdentifier, $methodDescription['parameters'], $methodDescription['body']));
    }
    createPHPFile($recordsDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', $generator->generate($recordClass));
}

// test activiteit
require $recordsDirectory  . DIRECTORY_SEPARATOR . 'activiteit.php';
$connection = new \PDO('mysql:dbname=teach', 'teach', 'teach', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
$schema = new \ActiveRecord\Schema($targetNamespace, $connection);
$record = $schema->select("activiteit", [])[0];
//print_r($record->inhoud);
$record->inhoud = uniqid();

print_r($schema->select("activiteit", [])[0]);
echo 'Done';