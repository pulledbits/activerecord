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
    
    $columns = [];
    foreach ($schemaManager->listTableColumns($tableName) as $columnName => $column) {
        $class->setProperty(PhpProperty::create($columnName));
        $columns[] = $columnName;
    }

    $querybuilder = $conn->createQueryBuilder();

    $class->setMethod(PhpMethod::create("connect")->setStatic(true)->setBody('return new \\PDO("' . $connectionParams['url'] . '");'));
    
    $class->setMethod(PhpMethod::create("fetchAll")->setStatic(true)->setBody(
        '$connection = self::connect();' . PHP_EOL .
        '$statement = $connection->query("' . $querybuilder->select($columns)->from($tableName) . '", \\PDO::FETCH_CLASS, "' . $escapedClassName . '", [$connection]);' . PHP_EOL .
        'return $statement->fetchAll();'
    ));
    
    $foreignKeys = $table->getForeignKeys();
    foreach ($foreignKeys as $foreignKeyIdentifier => $foreignKey) {
        $words = explode('_', $foreignKeyIdentifier);
        $camelCased = array_map('ucfirst', $words);
        $foreignKeyMethodIdentifier = join('', $camelCased);

        $foreignKeyMethod = PhpMethod::create("fetchBy" . $foreignKeyMethodIdentifier);
        $foreignKeyMethod->setStatic(true);
        
        $foreignKeyMapParameters = $foreignKeyWhere = [];
        foreach ($foreignKey->getLocalColumns() as $foreignKeyColumnName) {
            $foreignKeyMethod->addSimpleParameter($foreignKeyColumnName, "string");
            $foreignKeyWhere[] = $foreignKeyColumnName . ' = :' . $foreignKeyColumnName;
            $foreignKeyMapParameters[] = '$statement->bindParam(":' . $foreignKeyColumnName . '", $' . $foreignKeyColumnName . ', \\PDO::PARAM_STR);';
        }
        
        $foreignKeyMethod->setBody(
            '$connection = self::connect();' . PHP_EOL .
            '$statement = $connection->prepare("' . $querybuilder->select($columns)->from($tableName)->where(join(' AND ', $foreignKeyWhere)) . '", \\PDO::FETCH_CLASS, "' . str_replace("\\", "\\\\", $escapedClassName) . '", [$connection]);' . PHP_EOL .
            join(PHP_EOL . "\t", $foreignKeyMapParameters) . PHP_EOL .
            'return $statement->fetchAll();'
            );
        
        
        $class->setMethod($foreignKeyMethod);
    }
    
    $generator = new CodeGenerator();
    
    file_put_contents($tablesDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php' . PHP_EOL . $generator->generate($class));
}