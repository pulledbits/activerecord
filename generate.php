<?php

use gossi\codegen\model\PhpProperty;
use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use ActiveRecord\SourceTable;

require __DIR__ . '/vendor/autoload.php';

if ($_SERVER['argc'] < 3) {
    exit('please enter destination namespace and path');
}

$targetNamespace = $_SERVER['argv'][1];

$targetDirectory = $_SERVER['argv'][2];
$tablesDirectory = $targetDirectory . DIRECTORY_SEPARATOR . 'table';
if (file_exists($tablesDirectory) == false) {
    mkdir($tablesDirectory);
}
$recordsDirectory = $targetDirectory . DIRECTORY_SEPARATOR . 'record';
if (file_exists($recordsDirectory) == false) {
    mkdir($recordsDirectory);
}

$config = new \Doctrine\DBAL\Configuration();
//..
$connectionParams = array(
    'url' => 'mysql://teach:teach@localhost/teach'
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

function createMethod(string $identifier, array $parameters, string $body) {
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
    $method->setBody($body);
    return $method;
}

function generatePDOStatementBindParam(array $methodParameters) {
    return join(PHP_EOL, array_map(function($methodParameter) {
        return '$statement->bindParam(":' . $methodParameter . '", $' . $methodParameter . ', \\PDO::PARAM_STR);';
    }, $methodParameters)) . PHP_EOL;
}


$sourceSchema = new \ActiveRecord\SourceSchema($conn->getSchemaManager());
$generator = new CodeGenerator();

$schemaDescription = $sourceSchema->describe($targetNamespace);

$schemaClass = new gossi\codegen\model\PhpClass($schemaDescription['identifier']);
$schemaClass->setFinal(true);
$schemaClass->setMethod(createMethod("__construct", ["connection" => '\\PDO'], '$this->connection = $connection;'));

$schemaClass->setMethod(createMethod("select", ['tableIdentifier' => 'string', 'whereParameters' => 'array'],
    '$namedParameters = $where = [];' . PHP_EOL .
    'foreach ($whereParameters as $localColumn => $value) {' . PHP_EOL .
    '    $namedParameter = null;' . PHP_EOL .
    '    $where[] = $this->whereEquals($localColumn, $namedParameter);' . PHP_EOL .
    '    $namedParameters[$namedParameter] = $value;' . PHP_EOL .
    '}' . PHP_EOL .
    '$query = "SELECT * FROM " . $tableIdentifier;' . PHP_EOL .
    'if (count($where) > 0) {' . PHP_EOL .
    '   $query .= " WHERE " . join(" AND ", $where);' . PHP_EOL .
    '}' . PHP_EOL .
    '$statement = $this->connection->prepare($query);' . PHP_EOL .
    'foreach ($namedParameters as $namedParameter => $value) {' . PHP_EOL .
    '    $statement->bindParam(":" . $namedParameter, $value, \\PDO::PARAM_STR);' . PHP_EOL .
    '}' . PHP_EOL .
    'return $this->executeStatement($tableIdentifier, $statement);'
));
$schemaClass->setMethod(createMethod('whereEquals', ["columnIdentifier" => "string", "&namedParameter" => 'string'],
    '$namedParameter = ":" . uniqid();' . PHP_EOL .
    'return $columnIdentifier . " = " . $namedParameter;'
));

$executeCases = [];
foreach ($schemaDescription['tableClasses'] as $tableName => $tableClassDescription) {
    $executeCases[] = 'case "'. $tableName .'":' . PHP_EOL .
        '    return $statement->fetchAll(\\PDO::FETCH_CLASS, "' . $tableClassDescription['record-identifier'] . '", [new ' . $tableClassDescription['identifier'] . '($this->connection, $this)]);'
    ;

    $tableClass = new gossi\codegen\model\PhpClass($tableClassDescription['identifier']);
    $tableClass->setFinal(true);
    
    $tableClass->setProperty(PhpProperty::create("connection")->setType('\\PDO')->setVisibility('private'));
    $tableClass->setProperty(PhpProperty::create("schema")->setType($schemaDescription['identifier'])->setVisibility('private'));
    $tableClass->setMethod(createMethod("__construct", ["connection" => '\\PDO', 'schema' => $schemaDescription['identifier']],
        '$this->connection = $connection;' . PHP_EOL .
        '$this->schema = $schema;'
    ));

    $recordClass = new gossi\codegen\model\PhpClass($tableClassDescription['record-identifier']);
    $recordClass->setFinal(true);

    $recordClass->setProperty(PhpProperty::create("_table")->setType($tableClassDescription['identifier'])->setVisibility('private'));
    $defaultUpdateValues = [];
    $tableClassUpdateQuery = $conn->createQueryBuilder()->update($tableName);
    $tableClassUpdateParameters = [];
    foreach ($tableClassDescription['properties']['columns'] as $columnIdentifier) {
        $recordClass->setProperty(PhpProperty::create($columnIdentifier)->setType('string')->setVisibility('private'));
        $recordClassDefaultUpdateValues[] = '$this->' . $columnIdentifier;
        $tableClassUpdateParameters[$columnIdentifier] = "string";
        $tableClassUpdateQuery->set($columnIdentifier, ':' . $columnIdentifier);

    }

    $recordClass->setMethod(createMethod("__construct", ["table" => $tableClassDescription['identifier']], '$this->_table = $table;'));


    $tableClass->setMethod(createMethod("select", ['whereParameters' => 'array'],
        'return $this->schema->select("' . $tableName . '", $whereParameters);'
    ));
    $tableClass->setMethod(createMethod("update", $tableClassUpdateParameters,
        '$statement = $this->connection->prepare("' . $tableClassUpdateQuery->where('id = :pk_id')->getSQL() . '");' . PHP_EOL .
        '$statement->bindParam(":pk_id", $id, \\PDO::PARAM_STR);' . PHP_EOL . // TODO: make pk_id variable
        generatePDOStatementBindParam($tableClassDescription['properties']['columns']) . PHP_EOL .
        '$statement->execute();' . PHP_EOL .
        'return $statement->rowCount();'
    ));
    $recordClass->setMethod(createMethod("__set", ["property" => 'string', "value" => 'string'],
        'if (property_exists($this, $property)) {' . PHP_EOL .
        '$this->$property = $value;' . PHP_EOL .
        '$this->_table->update(' . join(',',$recordClassDefaultUpdateValues) . ');' . PHP_EOL .
        '}'
    ));

    foreach ($tableClassDescription['methods'] as $methodIdentifier => $methodDescription) {
        $tableClassFKMethod = PhpMethod::create($methodIdentifier);

        switch ($methodDescription['query'][0]) {
            case 'SELECT':
                $tableClassFKMethodArguments = [];
                foreach ($methodDescription['parameters'] as $methodParameter) {
                    $tableClassFKMethod->addParameter(PhpParameter::create($methodParameter)->setType('string'));
                    $tableClassFKMethodArguments[] = '$this->' . $methodParameter;
                }

                $whereParameters = [];
                foreach ($methodDescription['query'][1]['where'] as $referencedColumnName => $parameterIdentifier) {
                    $whereParameters[] = '\'' . $referencedColumnName . '\' => $' . $parameterIdentifier;
                }
                $tableClassFKMethod->setBody(
                    'return $this->schema->select("' . $methodDescription['query'][1]['from'] . '", [' . PHP_EOL . join(',' . PHP_EOL, $whereParameters) . PHP_EOL . ']);' . PHP_EOL);

                $tableClass->setMethod($tableClassFKMethod);

                $recordClassFKMethod = PhpMethod::create($methodIdentifier);
                $recordClassFKMethod->setBody(
                    'return $this->_table->' . $methodIdentifier . '(' . join(', ', $tableClassFKMethodArguments) . ');'
                );
                $recordClass->setMethod($recordClassFKMethod);
                break;
        }

    }
    file_put_contents($tablesDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php' . PHP_EOL . $generator->generate($tableClass));
    file_put_contents($recordsDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php' . PHP_EOL . $generator->generate($recordClass));
}


$schemaClass->setMethod(createMethod('executeStatement', ['tableIdentifier' => 'string', 'statement' => '\\PDOStatement'],
        '$statement->execute();' . PHP_EOL .
        'switch ($tableIdentifier) {' . PHP_EOL .
            join (PHP_EOL, $executeCases) .
        '}'
    )->setVisibility('private'));

file_put_contents($targetDirectory . DIRECTORY_SEPARATOR . 'Schema.php', '<?php' . PHP_EOL . $generator->generate($schemaClass));

// test activiteit
require $targetDirectory  . DIRECTORY_SEPARATOR . 'Schema.php';
require $tablesDirectory  . DIRECTORY_SEPARATOR . 'activiteit.php';
require $recordsDirectory  . DIRECTORY_SEPARATOR . 'activiteit.php';
$connection = new \PDO('mysql:dbname=teach', 'teach', 'teach', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
$schema = new \Database\Schema($connection);
$table = new \Database\Table\activiteit($connection, $schema);
$record = $table->fetchAll()[0];
$record->inhoud = uniqid();

print_r($table->fetchAll()[0]);
echo 'Done';