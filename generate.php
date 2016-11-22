<?php

use gossi\codegen\model\PhpProperty;
use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use phootwork\file\Directory;

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
    'url' => 'sqlite:///' . __DIR__ . '/database.sqlite'
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$schemaManager = $conn->getSchemaManager();

foreach ($schemaManager->listTableNames() as $tableName) {
    $className = $targetNamespace . "Table\\" . $tableName;
    
    $class = new gossi\codegen\model\PhpClass($className);
    $class->setFinal(true);
    
    $class->setProperty(PhpProperty::create("connection")->setType("\PDO"));
    $class->setMethod(PhpMethod::create("__construct")->setParameters([PhpParameter::create("connection")->setType("\\PDO")])->setBody('$this->connection = $connection;'));
    
    $columns = [];
    foreach ($schemaManager->listTableColumns($tableName) as $columnName => $column) {
        $class->setProperty(PhpProperty::create($columnName));
        $columns[] = $columnName;
    }

    $querybuilder = $conn->createQueryBuilder();
    
    $class->setMethod(PhpMethod::create("fetchAll")->setStatic(true)->setBody(
        '$connection = new \\PDO("' . $connectionParams['url'] . '");' . PHP_EOL .
        '$statement = $connection->query("' . $querybuilder->select($columns)->from($tableName) . '", \\PDO::FETCH_CLASS, "' . str_replace("\\", "\\\\", $className) . '", [$connection]);' . PHP_EOL .
        'return $statement->fetchAll();'
    ));
    
    $generator = new CodeGenerator();
    
    file_put_contents($tablesDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php' . PHP_EOL . $generator->generate($class));
}