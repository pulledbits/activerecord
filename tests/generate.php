<?php
$targetNamespace = '\\Database';
$targetDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'gen';

if (file_exists($targetDirectory) === false) {
    mkdir($targetDirectory);
}

$phpbin = trim(`which php`);
$command = $phpbin . ' ' . dirname(__DIR__) . DIRECTORY_SEPARATOR . 'generate.php ' . $targetNamespace . ' ' . $targetDirectory . ' ' . $_SERVER['argv'][1];
echo 'Running ' . $command . '...' . PHP_EOL;
passthru($command);

// test activiteit
require __DIR__ . '/bootstrap.php';
$url = parse_url($_SERVER['argv'][1]);
$connection = new \PDO($url['scheme'] . ':dbname=' . substr($url['path'], 1), $url['user'], $url['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));

$recordConfigurator = require $targetDirectory . DIRECTORY_SEPARATOR . 'factory.php';

$schema = new \ActiveRecord\Schema($recordConfigurator, $connection);

$table = new \ActiveRecord\Schema\Asset("blok", $schema);
assert(count($table->select(['_collegejaar' => 'collegejaar', '_nummer' => 'nummer'], ['collegejaar' => '1415', 'nummer' => '2'])) === 0, 'no previous record exists');
$record = $table->insert(['collegejaar' => '1415', 'nummer' => '1'], [])[0];
assert($record->nummer === '1', 'record is properly initialized');
$record->nummer = '2';
assert($record->nummer === $table->select(['_collegejaar' => 'collegejaar', '_nummer' => 'nummer'], ['collegejaar' => '1415', 'nummer' => '2'])[0]->nummer, 'record is properly updated');
assert(count($record->delete()) > 1, 'delete confirms removal');

require $targetDirectory . DIRECTORY_SEPARATOR . 'Record' . DIRECTORY_SEPARATOR . 'leerdoelenview.class.php';
$viewRecord = $schema->selectFrom("leerdoelenview", ['*'], [], function(\Closure $recordConfigurator) use ($schema) {
    return $recordConfigurator(new \ActiveRecord\Schema\Asset('leerdoelenview', $schema));
});

assert(count($viewRecord) > 1, 'view records exist');
echo 'Done testing';