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

$schemaClass = new gossi\codegen\model\PhpClass($schemaDescription['identifier']);
$schemaClass->setFinal(true);
$schemaClass->setMethod(createMethod("__construct", ["connection" => '\\PDO'], ['$this->connection = $connection;']));

$schemaClass->setMethod(createMethod("select", ['recordClassIdentifier' => 'string', 'whereParameters' => 'array'], [
    '$namedParameters = $where = [];',
    'foreach ($whereParameters as $localColumn => $value) {',
    '    $namedParameter = null;',
    '    $where[] = $this->whereEquals($localColumn, $namedParameter);',
    '    $namedParameters[$namedParameter] = $value;',
    '}',
    '$query = "SELECT * FROM " . join("", array_slice(explode("\\\\", $recordClassIdentifier), -1));',
    'if (count($where) > 0) {',
    '   $query .= " WHERE " . join(" AND ", $where);',
    '}',
    'echo $query;',
    '$statement = $this->connection->prepare($query);',
    'foreach ($namedParameters as $namedParameter => $value) {',
    '    $statement->bindParam($namedParameter, $value, \\PDO::PARAM_STR);',
    '}',
    '$statement->execute();',
    'return $statement->fetchAll(\\PDO::FETCH_CLASS, $recordClassIdentifier, [$this]);'
]));
$schemaClass->setMethod(createMethod("update", ['recordClassIdentifier' => "string", 'setParameters' => "array", 'whereParameters' => 'array'], [
    '$namedParameters = [];',
    '$set = [];',
    'foreach ($setParameters as $localColumn => $value) {',
    '    $namedParameter = null;',
    '    $set[] = $this->whereEquals($localColumn, $namedParameter);',
    '    $namedParameters[$namedParameter] = $value;',
    '}',
    '$where = [];',
    'foreach ($whereParameters as $localColumn => $value) {',
    '    $namedParameter = null;',
    '    $where[] = $this->whereEquals($localColumn, $namedParameter);',
    '    $namedParameters[$namedParameter] = $value;',
    '}',
    '$query = "UPDATE " . join("", array_slice(explode("\\\\", $recordClassIdentifier), -1)) . " SET " . join(", ", $set);',
    'if (count($where) > 0) {',
    '   $query .= " WHERE " . join(" AND ", $where);',
    '}',

    '$statement = $this->connection->prepare($query);',
    'foreach ($namedParameters as $namedParameter => $value) {',
    '    $statement->bindParam($namedParameter, $value, is_null($value) ? \\PDO::PARAM_NULL : \\PDO::PARAM_STR);',
    '}',
    '$statement->execute();',
    'return $statement->rowCount();'
]));
$schemaClass->setMethod(createMethod('whereEquals', ["columnIdentifier" => "string", "&namedParameter" => 'string'], [
    '$namedParameter = ":" . uniqid();',
    'return $columnIdentifier . " = " . $namedParameter;'
]));

foreach ($schemaDescription['recordClasses'] as $tableName => $recordClassDescription) {
    $recordClass = new gossi\codegen\model\PhpClass($recordClassDescription['identifier']);
    $recordClass->setFinal(true);

    $recordClassDefaultUpdateValues = [];
    foreach ($recordClassDescription['properties']['columns'] as $columnIdentifier) {
        $recordClass->setProperty(PhpProperty::create($columnIdentifier)->setType('string')->setVisibility('private'));
        $recordClassDefaultUpdateValues[] = '\'' . $columnIdentifier . '\' => $this->' . $columnIdentifier;
    }

    $recordClass->setProperty(PhpProperty::create("schema")->setType($schemaDescription['identifier'])->setVisibility('private'));
    $recordClass->setMethod(createMethod("__construct", ["schema" => $schemaDescription['identifier']], ['$this->schema = $schema;']));

    $recordClass->setMethod(createMethod("__set", ["property" => 'string', "value" => 'string'], [
        'if (property_exists($this, $property)) {',
        '$this->$property = $value;',
        '$this->schema->update(__CLASS__, [' . join(',' . PHP_EOL, $recordClassDefaultUpdateValues) . '], ["id" => $this->id]);',
        '}'
    ]));

    foreach ($recordClassDescription['methods'] as $methodIdentifier => $methodDescription) {
        $recordClassFKMethod = PhpMethod::create($methodIdentifier);

        switch ($methodDescription['query'][0]) {
            case 'SELECT':
                $whereParameters = [];
                foreach ($methodDescription['query'][1]['where'] as $referencedColumnName => $parameterIdentifier) {
                    $whereParameters[] = '\'' . $referencedColumnName . '\' => $this->' . $parameterIdentifier;
                }
                $recordClass->setMethod(createMethod($methodIdentifier, [], [
                    'return $this->schema->select(__NAMESPACE__ . "\\' . $methodDescription['query'][1]['from'] . '", [', join(',' . PHP_EOL, $whereParameters), ']);'
                ]));
                break;
        }

    }
    createPHPFile($recordsDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', $generator->generate($recordClass));
}

createPHPFile($targetDirectory . DIRECTORY_SEPARATOR . 'Schema.php', $generator->generate($schemaClass));

// test activiteit
require $targetDirectory  . DIRECTORY_SEPARATOR . 'Schema.php';
require $recordsDirectory  . DIRECTORY_SEPARATOR . 'activiteit.php';
$connection = new \PDO('mysql:dbname=teach', 'teach', 'teach', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
$schema = new \Database\Schema($connection);
$record = $schema->select($targetNamespace . "\\Record\\activiteit", [])[0];
//print_r($record->inhoud);
$record->inhoud = uniqid();

print_r($schema->select($targetNamespace . "\\Record\\activiteit", [])[0]);
echo 'Done';