<?php

use gossi\codegen\model\PhpProperty;
use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use phootwork\file\Directory;
use ActiveRecord\SourceTable;

require __DIR__ . '/vendor/autoload.php';

if ($_SERVER['argc'] < 3) {
    exit('please enter destination namespace and path');
}

$targetNamespace = $_SERVER['argv'][1];
if (substr($targetNamespace, -1) != "\\") {
    $targetNamespace .= "\\";
}

$targetDirectory = $_SERVER['argv'][2];
$tablesDirectory = $targetDirectory . DIRECTORY_SEPARATOR . 'table';
if (file_exists($tablesDirectory) == false) {
    mkdir($tablesDirectory);
}

$config = new \Doctrine\DBAL\Configuration();
//..
$connectionParams = array(
    'url' => 'mysql://teach:teach@localhost/teach'
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$schemaManager = $conn->getSchemaManager();

foreach ($schemaManager->listTables() as $table) {
    $tableName = $table->getName();
    
    $tableDescriptor = new SourceTable($table);
    $classDescription = $tableDescriptor->describe($targetNamespace . "Table");
    
    $class = new gossi\codegen\model\PhpClass($classDescription['identifier']);
    $class->setFinal(true);
    
    $class->setProperty(PhpProperty::create("connection")->setType('\\PDO'));
    $constructor = PhpMethod::create("__construct");
    $constructor->addSimpleParameter("connection", '\\PDO');
    $class->setMethod($constructor);
    
    $constructorBody = ['$this->connection = $connection;'];
    foreach ($classDescription['properties'] as $propertyIdentifier => $value) {
        $class->setProperty(PhpProperty::create($propertyIdentifier));
        $constructorBody[] = '$this->' . $propertyIdentifier . ' = ' . var_export($value, true) . ';';
    }
    $constructor->setBody(join(PHP_EOL, $constructorBody));

    $querybuilder = $conn->createQueryBuilder();
    $foreignKeys = $table->getForeignKeys();
    foreach ($classDescription['methods'] as $methodIdentifier => $methodDescription) {
        $method = PhpMethod::create($methodIdentifier);
        $method->setParameters(array_map(function($methodParameter) {
            return PhpParameter::create($methodParameter)->setType('string'); 
        }, $methodDescription['parameters']));
        
        $query = $querybuilder->select($methodDescription['query'][1]['fields']);
        $query->from($methodDescription['query'][1]['from']);
        if (strlen($methodDescription['query'][1]['where']) > 0) {
            $query->where($methodDescription['query'][1]['where']);
        }
        
        $method->setBody(
            '$statement = $this->connection->prepare("' . $query . '", \\PDO::FETCH_CLASS, "' . $class->getName() . '", [$connection]);' . PHP_EOL .
            join(PHP_EOL, array_map(function($methodParameter) {
                return '$statement->bindParam(":' . $methodParameter . '", $' . $methodParameter . ', \\PDO::PARAM_STR);';
            }, $methodDescription['parameters'])) . PHP_EOL .
            'return $statement->fetchAll();'
            );
        
        
        $class->setMethod($method);
    }
    
    $generator = new CodeGenerator();
    
    file_put_contents($tablesDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php' . PHP_EOL . $generator->generate($class));
}