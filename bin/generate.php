<?php

$applicationBootstrap = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';
$applicationBootstrap();

if ($_SERVER['argc'] < 3) {
    exit('please enter destination namespace and path');
}

$targetNamespace = $_SERVER['argv'][1] . '\\Record';

$targetDirectory = $_SERVER['argv'][2];
if (file_exists($targetDirectory) == false) {
    mkdir($targetDirectory);
}

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

$sourceSchema = new \ActiveRecord\SQL\Source\Schema($conn->getSchemaManager());
$schemaDescription = $sourceSchema->describe(new \ActiveRecord\SQL\Source\Table($targetNamespace));
foreach ($schemaDescription as $tableName => $recordClassDescription) {
    file_put_contents($recordsDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php return function(\ActiveRecord\Schema $schema, string $entityTypeIdentifier, array $values) {
    $keys = '.var_export($recordClassDescription['identifier'], true).';
    $sliced = [];
    foreach ($values as $key => $value) {
        if (in_array($key, $keys, true)) {
            $sliced[$key] = $value;
        }
    }
    return new \ActiveRecord\Entity($schema, $entityTypeIdentifier, $sliced, '.var_export($recordClassDescription['references'], true).', $values);
};');
}

file_put_contents($targetDirectory . DIRECTORY_SEPARATOR . 'factory.php', '<?php return new \ActiveRecord\RecordFactory(__DIR__ . DIRECTORY_SEPARATOR . \'Record\');');

echo 'Done' . PHP_EOL;