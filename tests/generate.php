<?php

if (ini_get('zend.assertions') != 1) {
    exit('zend.assertions must be enabled');
}
assert_options(ASSERT_ACTIVE, true);
assert_options(ASSERT_WARNING, true);
assert_options(ASSERT_BAIL, true);


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

$recordConfigurator = new \ActiveRecord\RecordFactory($targetDirectory);

$schema = new \ActiveRecord\SQL\Schema($recordConfigurator, $connection);

$starttijd = date('Y-m-d ') . '23:00:00';

$schema->delete('contactmoment', ['starttijd' => $starttijd, 'les_id' => '1']);
$schema->delete('contactmoment', ['starttijd' => $starttijd, 'les_id' => '2']);
assert(count($schema->read('contactmoment', [], ['starttijd' => $starttijd, 'les_id' => '2'])) === 0, 'previous record exists');
assert($schema->create('contactmoment', ['starttijd' => $starttijd, 'les_id' => '1']) === 1, 'no record created');
$record = $schema->read('contactmoment', [], ['starttijd' => $starttijd, 'les_id' => '1'])[0];
assert($record->les_id === '1', 'record is properly initialized');
$record->les_id = '2';
assert($record->les_id === $schema->read('contactmoment', [], ['starttijd' => $starttijd, 'les_id' => '2'])[0]->les_id, 'record is properly updated');

$viewRecord = $schema->read("contactmoment_vandaag", [], []);
assert(count($viewRecord) === 1, 'view records exist');

assert(count($record->delete()) === 1, 'delete confirms removal');
echo 'Done testing';