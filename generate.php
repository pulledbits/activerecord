<?php

use gossi\codegen\model\PhpProperty;
use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use phootwork\file\Directory;

require __DIR__ . '/vendor/autoload.php';

if ($_SERVER['argc'] == 1) {
    exit('please enter destination path');
}
$targetDirectory = $_SERVER['argv'][1];
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
    $class = new gossi\codegen\model\PhpClass("Database\\Table\\" . $tableName);
    $class->setFinal(true);
    
    $class->setProperty(PhpProperty::create("connection")->setType("\PDO"));
    $class->setMethod(PhpMethod::create("__construct")->setParameters([PhpParameter::create("connection")->setType("\\PDO")])->setBody('$this->connection = $connection;'));
    
    foreach ($schemaManager->listTableColumns($tableName) as $columName => $column) {
        $class->setProperty(PhpProperty::create($columName));
    }

    $class->setMethod(PhpMethod::create("fetchAll")->setStatic(true)->setBody(
        '$this->connection->query("' . $conn->createQueryBuilder()->select("*")->from($tableName) . '");'));
    
    $generator = new CodeGenerator();
    
    file_put_contents($tablesDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php' . PHP_EOL . $generator->generate($class));
}