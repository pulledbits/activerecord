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
foreach ($schemaDescription as $entityTypeIdentifier => $recordClassDescription) {
    $targetFile = $targetDirectory . DIRECTORY_SEPARATOR . $entityTypeIdentifier . '.php';
    if (array_key_exists('entityTypeIdentifier', $recordClassDescription)) {
        $generator = new \pulledbits\ActiveRecord\Source\WrappedEntityGeneratorGenerator($recordClassDescription['entityTypeIdentifier']);
        file_put_contents($targetFile, $generator->generate());
        continue;
    }

    $generator = new \pulledbits\ActiveRecord\Source\EntityGeneratorGenerator($recordClassDescription['identifier'], $recordClassDescription['requiredColumnIdentifiers'], $recordClassDescription['references']);
    file_put_contents($targetFile, $generator->generate());
}

echo 'Done' . PHP_EOL;