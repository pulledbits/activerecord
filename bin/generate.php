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
} else {
    foreach (glob($recordsDirectory . DIRECTORY_SEPARATOR . '*.php') as $recordFile) {
        unlink($recordFile);
    }
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
    if (array_key_exists('entityTypeIdentifier', $recordClassDescription)) {
        file_put_contents($recordsDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php return require __DIR__ . DIRECTORY_SEPARATOR . "' . $recordClassDescription['entityTypeIdentifier'] . '.php";');
    } else {

        $references = [];
        foreach ($recordClassDescription['references'] as $referenceIdentifier => $reference) {
            $references[] = '$record->references("' . $referenceIdentifier .'", "' . $reference['table'] . '", ' . var_export($reference['where'], true) . ');';
        }

        file_put_contents($recordsDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php return function(\ActiveRecord\Schema $schema, string $entityTypeIdentifier) {
    $record = new \ActiveRecord\Entity($schema, $entityTypeIdentifier, '.var_export($recordClassDescription['identifier'], true).');
    ' . join(PHP_EOL . '    ', $references) . '
    return $record;
};');
    }

}

file_put_contents($targetDirectory . DIRECTORY_SEPARATOR . 'factory.php', '<?php return new \ActiveRecord\RecordFactory(__DIR__ . DIRECTORY_SEPARATOR . \'Record\');');

echo 'Done' . PHP_EOL;