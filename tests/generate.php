<?php
$targetNamespace = '\\Database';
$targetDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'gen';

if (file_exists($targetDirectory) === false) {
    mkdir($targetDirectory);
}

$phpbin = trim(`which php`);
$command = $phpbin . ' ' . dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'generate.php ' . $targetNamespace . ' ' . $targetDirectory . ' ' . $_SERVER['argv'][1];
echo 'Running ' . $command . '...' . PHP_EOL;
passthru($command);

// test activiteit
require __DIR__ . '/bootstrap.php';
$url = parse_url($_SERVER['argv'][1]);
$connection = new \PDO($url['scheme'] . ':dbname=' . substr($url['path'], 1), $url['user'], $url['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));

$recordConfigurator = require $targetDirectory . DIRECTORY_SEPARATOR . 'factory.php';

$schema = new \ActiveRecord\SQL\Schema($recordConfigurator, $connection);

assert(count($schema->read('blok', ['_collegejaar' => 'collegejaar', '_nummer' => 'nummer'], ['collegejaar' => '1415', 'nummer' => '2'])) === 0, 'no previous record exists');
$schema->create('blok', ['collegejaar' => '1415', 'nummer' => '1'], []);
$record = $schema->read('blok', ['collegejaar', 'nummer'], ['collegejaar' => '1415', 'nummer' => '1'])[0];
assert($record->nummer === '1', 'record is properly initialized');
$record->nummer = '2';
assert($record->nummer === $schema->read('blok', ['_collegejaar' => 'collegejaar', '_nummer' => 'nummer'], ['collegejaar' => '1415', 'nummer' => '2'])[0]->nummer, 'record is properly updated');
assert(count($record->delete()) > 1, 'delete confirms removal');

$viewRecord = $schema->read("leerdoelenview", ['*'], []);

assert(count($viewRecord) > 1, 'view records exist');
echo 'Done testing';