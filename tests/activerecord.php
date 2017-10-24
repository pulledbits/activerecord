<?php

if (ini_get('zend.assertions') != 1) {
    exit('zend.assertions must be enabled');
}
assert_options(ASSERT_ACTIVE, true);
assert_options(ASSERT_WARNING, true);
assert_options(ASSERT_BAIL, true);

$targetDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'activerecord';

if (file_exists($targetDirectory) === false) {
    mkdir($targetDirectory);
}

$phpbin = trim(`which php`);
$command = $phpbin . ' ' . dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'activerecord.php ' . $targetDirectory . ' ' . $_SERVER['argv'][1];
echo 'Running ' . $command . '...' . PHP_EOL;
passthru($command);

// test activiteit
require __DIR__ . '/bootstrap.php';
$recordConfigurator = new \pulledbits\ActiveRecord\RecordFactory(\pulledbits\ActiveRecord\Source\SQL\Schema::fromDatabaseURL($_SERVER['argv'][1]), $targetDirectory);

$url = parse_url($_SERVER['argv'][1]);
$connection = new \PDO($url['scheme'] . ':dbname=' . substr($url['path'], 1), $url['user'], $url['pass'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
$schema = new \pulledbits\ActiveRecord\SQL\Schema($recordConfigurator, new \pulledbits\ActiveRecord\SQL\Connection($connection));

$starttijd = date('Y-m-d ') . '23:00:00';

$schema->delete('contactmoment', ['starttijd' => $starttijd, 'les_id' => '1']);
$schema->delete('contactmoment', ['starttijd' => $starttijd, 'les_id' => '2']);
assert(count($schema->read('contactmoment', [], ['starttijd' => $starttijd, 'les_id' => '2'])) === 0, 'previous record exists');
assert($schema->create('contactmoment', ['starttijd' => $starttijd, 'les_id' => '1', 'owner' => 'hameijer']) === 1, 'no record created');
/**
 * @var $record \pulledbits\ActiveRecord\Record
 */
$record = $schema->read('contactmoment', [], ['starttijd' => $starttijd, 'les_id' => '1'])[0];
assert($record->les_id === '1', 'record is properly initialized');
$record->les_id = '2';
assert($record->les_id === $schema->read('contactmoment', [], ['starttijd' => $starttijd, 'les_id' => '2'])[0]->les_id, 'record is properly updated');

$viewRecord = $schema->readFirst("contactmoment_vandaag", [], ['starttijd' => $starttijd]);
assert($viewRecord->starttijd === $starttijd, 'view records exist');

assert($record->delete() === 1, 'delete confirms removal');
echo 'Done testing';