<?php

use gossi\codegen\model\PhpProperty;
use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;

require __DIR__ . '/vendor/autoload.php';

if ($_SERVER['argc'] < 3) {
    exit('please enter destination namespace and path');
}

$targetNamespace = $_SERVER['argv'][1] . '\\Record';

$targetDirectory = $_SERVER['argv'][2];
$recordsDirectory = $targetDirectory . DIRECTORY_SEPARATOR . 'Record';
if (file_exists($recordsDirectory) == false) {
    mkdir($recordsDirectory);
}

$config = new \Doctrine\DBAL\Configuration();
$connectionParams = array(
    'url' => 'mysql://teach:teach@localhost/teach'
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$generator = new CodeGenerator();

$sourceSchema = new \ActiveRecord\Source\Schema($conn->getSchemaManager());
$schemaDescription = $sourceSchema->describe(new \ActiveRecord\Source\Table($targetNamespace));
foreach ($schemaDescription['recordClasses'] as $tableName => $recordClassDescription) {
    $recordClass = new gossi\codegen\model\PhpClass($recordClassDescription['identifier']);
    $recordClass->setFinal(true);

    foreach ($recordClassDescription['properties'] as $propertyIdentifier => $propertyType) {
        $property = PhpProperty::create($propertyIdentifier);
        $property->setType($propertyType);
        $property->setVisibility('private');
        $recordClass->setProperty($property);
    }

    foreach ($recordClassDescription['methods'] as $methodIdentifier => $methodDescription) {
        $method = PhpMethod::create($methodIdentifier);
        foreach ($methodDescription['parameters'] as $methodParameterIdentifier => $methodParameterType) {
            $parameter = PhpParameter::create($methodParameterIdentifier);
            $parameter->setType($methodParameterType);
            $method->addParameter($parameter);
        }
        $method->setBody(join(PHP_EOL, $methodDescription['body']));
        $recordClass->setMethod($method);
    }
    file_put_contents($recordsDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php' . PHP_EOL . $generator->generate($recordClass));
}

// test activiteit
require $recordsDirectory  . DIRECTORY_SEPARATOR . 'activiteit.php';
$connection = new \PDO('mysql:dbname=teach', 'teach', 'teach', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
$schema = new \ActiveRecord\Schema($targetNamespace, $connection);
$record = $schema->select("activiteit", ['_id' => 'id', '_inhoud' => 'inhoud'], [])[0];
//print_r($record->inhoud);
$record->inhoud = uniqid();

print_r($schema->select("activiteit", ['_id' => 'id', '_inhoud' => 'inhoud'], [])[0]);
echo 'Done';