<?php

use gossi\codegen\model\PhpProperty;
use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use phootwork\file\Directory;
use ActiveRecord\Table;

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
    
    $tableDescriptor = new Table($table);
    $classDescription = $tableDescriptor->describe($targetNamespace . "Table");
    
    $class = new gossi\codegen\model\PhpClass($classDescription['identifier']);
    $class->setFinal(true);
    
    $escapedClassName = str_replace("\\", "\\\\", $class->getQualifiedName());
    
    $class->setProperty(PhpProperty::create("connection")->setType("\PDO"));
    $class->setMethod(PhpMethod::create("__construct")->setParameters([PhpParameter::create("connection")->setType("\\PDO")])->setBody('$this->connection = $connection;'));
    
    foreach ($classDescription['properties'] as $propertyIdentifier => $value) {
        $class->setProperty(PhpProperty::create($propertyIdentifier));
    }

    $querybuilder = $conn->createQueryBuilder();
    
    $class->setMethod(PhpMethod::create("fetchAll")->setBody(
        '$statement = $this->connection->query("' . $querybuilder->select('*')->from($tableName) . '", \\PDO::FETCH_CLASS, "' . $escapedClassName . '", [$connection]);' . PHP_EOL .
        'return $statement->fetchAll();'
    ));
    
    $foreignKeys = $table->getForeignKeys();
    foreach ($classDescription['methods'] as $methodIdentifier => $method) {
        $foreignKeyMethod = PhpMethod::create($methodIdentifier);
        
        $foreignKeyMapParameters = $foreignKeyWhere = [];
        foreach ($method['parameters'] as $methodParameter) {
            $foreignKeyMethod->addSimpleParameter($methodParameter, "string");
            $foreignKeyWhere[] = $methodParameter . ' = :' . $methodParameter;
            $foreignKeyMapParameters[] = '$statement->bindParam(":' . $methodParameter . '", $' . $methodParameter . ', \\PDO::PARAM_STR);';
        }
        
        $foreignKeyMethod->setBody(
            '$statement = $this->connection->prepare("' . $querybuilder->select('*')->from($tableName)->where(join(' AND ', $foreignKeyWhere)) . '", \\PDO::FETCH_CLASS, "' . str_replace("\\", "\\\\", $escapedClassName) . '", [$connection]);' . PHP_EOL .
            join(PHP_EOL . "\t", $foreignKeyMapParameters) . PHP_EOL .
            'return $statement->fetchAll();'
            );
        
        
        $class->setMethod($foreignKeyMethod);
    }
    
    $generator = new CodeGenerator();
    
    file_put_contents($tablesDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php' . PHP_EOL . $generator->generate($class));
}