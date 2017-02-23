<?php

$applicationBootstrap = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

if ($_SERVER['argc'] < 2) {
    exit('please enter destination namespace and path');
}

$targetDirectory = $_SERVER['argv'][1];
if (file_exists($targetDirectory) == false) {
    mkdir($targetDirectory);
} else {
    foreach (glob($targetDirectory . DIRECTORY_SEPARATOR . '*.php') as $recordFile) {
        unlink($recordFile);
    }
}

if ($_SERVER['argc'] === 3) {
    $dburl = $_SERVER['argv'][2];
} else {
    $dbhost = readline('Please enter database hostname: ');
    $dbname = readline('Please enter database name on ' . $dbhost . ': ');
    $dbuser = readline('Please enter username for ' . $dbname . ': ');
    $dbpass = readline('Please enter password for ' . $dbuser . '@' . $dbhost . '/' . $dbname . ': ');
    $dburl = 'mysql://' . $dbuser . ':' . $dbpass . '@' . $dbhost . '/' . $dbname;
}

$sourceSchema = $applicationBootstrap->sourceSchema($dburl);
$schemaDescription = $sourceSchema->describe(new \pulledbits\ActiveRecord\SQL\Source\Table());
foreach ($schemaDescription as $tableName => $recordClassDescription) {
    if (array_key_exists('entityTypeIdentifier', $recordClassDescription)) {
        file_put_contents($targetDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php return require __DIR__ . DIRECTORY_SEPARATOR . "' . $recordClassDescription['entityTypeIdentifier'] . '.php";');
    } else {

        $references = [];
        foreach ($recordClassDescription['references'] as $referenceIdentifier => $reference) {
            $references[] = '$record->references("' . $referenceIdentifier .'", "' . $reference['table'] . '", ' . var_export($reference['where'], true) . ');';
        }

        file_put_contents($targetDirectory . DIRECTORY_SEPARATOR . $tableName . '.php', '<?php return function(\pulledbits\ActiveRecord\Schema $schema, string $entityTypeIdentifier) {
    $record = new \pulledbits\ActiveRecord\Entity($schema, $entityTypeIdentifier, '.var_export($recordClassDescription['identifier'], true).');
    $record->requires('.var_export($recordClassDescription['requiredColumnIdentifiers'], true).');
    ' . join(PHP_EOL . '    ', $references) . '
    return $record;
};');
    }

}

echo 'Done' . PHP_EOL;