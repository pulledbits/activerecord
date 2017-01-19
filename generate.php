<?php

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
    $recordClass->setInterfaces($recordClassDescription['interfaces']);
    $recordClass->setTraits($recordClassDescription['traits']);
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
    file_put_contents($recordsDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php' . PHP_EOL . $generator->generate($recordClass));
});
echo 'Done' . PHP_EOL;