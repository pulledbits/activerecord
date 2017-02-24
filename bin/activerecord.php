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
$generatorGeneratorFactory = $applicationBootstrap->generatorGeneratorFactory();
$entityTypes = $sourceSchema->describe(new \pulledbits\ActiveRecord\SQL\Source\Table(), $generatorGeneratorFactory);

$reversedLinkedEntityTypes = $entityTypes;
foreach ($entityTypes as $entityTypeIdentifier => $recordClassDescription) {
    if (array_key_exists('references', $recordClassDescription) === false) {
        continue;
    }

    foreach ($recordClassDescription['references'] as $referenceIdentifier => $reference) {
        $reversedLinkedEntityTypes[$reference['table']]['references'][$referenceIdentifier] = $generatorGeneratorFactory->makeReference($entityTypeIdentifier, array_flip($reference['where']));
    }
}
foreach ($reversedLinkedEntityTypes as $entityTypeIdentifier => $recordClassDescription) {
    $targetFile = $targetDirectory . DIRECTORY_SEPARATOR . $entityTypeIdentifier . '.php';
    $generator = $generatorGeneratorFactory->makeGeneratorGenerator($recordClassDescription);
    file_put_contents($targetFile, $generator->generate());
}

echo 'Done' . PHP_EOL;