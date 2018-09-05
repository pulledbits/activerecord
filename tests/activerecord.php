<?php

use pulledbits\ActiveRecord\SQL\Meta\SchemaFactory;

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

// test activiteit
require __DIR__ . '/bootstrap.php';

$parsedUrl = parse_url($_SERVER['argv'][1]);
$pdo = new \PDO($parsedUrl['scheme'] . ':', $parsedUrl['user'], $parsedUrl['pass'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));

$connection = new \pulledbits\ActiveRecord\SQL\Connection($pdo);
$schema = $connection->schema(substr($parsedUrl['path'], 1));

$starttijd = date('Y-m-d ') . '23:00:00';

$schema->delete('contactmoment', ['starttijd' => $starttijd, 'les_id' => '1']);
$schema->delete('contactmoment', ['starttijd' => $starttijd, 'les_id' => '2']);
assert(count($schema->read('contactmoment', [], ['starttijd' => $starttijd, 'les_id' => '2'])) === 0, 'previous record exists');
assert($schema->create('contactmoment', ['starttijd' => $starttijd, 'les_id' => '1', 'owner' => 'hameijer']) === 1, 'no record created');
/**
 * @var $record \pulledbits\ActiveRecord\Entity
 */
$record = $schema->read('contactmoment', [], ['starttijd' => $starttijd, 'les_id' => '1'])[0];
assert($record->les_id === '1', 'record is properly initialized');
$record->les_id = '2';
assert($record->les_id === $schema->read('contactmoment', [], ['starttijd' => $starttijd, 'les_id' => '2'])[0]->les_id, 'record is properly updated');

$viewRecords = $schema->read("contactmoment_vandaag", [], ['starttijd' => $starttijd]);
assert($viewRecords[0]->starttijd === $starttijd, 'view records exist');

assert($record->delete() === 1, 'delete confirms removal');
echo 'Done testing';


echo PHP_EOL . 'Cleanup...';
foreach (glob($targetDirectory . DIRECTORY_SEPARATOR . "*") as $file) {
    if (substr($file, 0, 1) !== '.') {
        unlink($file);
    }
}
rmdir($targetDirectory);
echo 'DONE';