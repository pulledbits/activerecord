<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use pulledbits\ActiveRecord\Source\TableDescription;
use pulledbits\ActiveRecord\SQL\Connection;

class SchemaFactory
{
    public static function makeFromPDO(Connection $connection, \PDO $pdo): Schema
    {
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = ['pdo' => $pdo];
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        return self::makeFromSchemaManager($connection, $conn->getSchemaManager());
    }

    public static function makeFromSchemaManager(Connection $connection, AbstractSchemaManager $schemaManager)
    {
        $sourceTable = new \pulledbits\ActiveRecord\SQL\Meta\Table();
        $tables = [];
        foreach ($schemaManager->listTables() as $dbalTable) {
            $tables[$dbalTable->getName()] = $sourceTable->describe($dbalTable);
        }
        $prototypeTables = $tables;
        foreach ($tables as $tableName => $recordClassDescription) {
            foreach ($recordClassDescription->references as $referenceIdentifier => $reference) {
                if (array_key_exists($reference['table'], $prototypeTables) === false) {
                    $prototypeTables[$reference['table']] = new TableDescription();
                }
                $prototypeTables[$reference['table']]->references[$referenceIdentifier] = $sourceTable->makeReference($tableName, array_flip($reference['where']));
            }
        }

        $prototypeViews = [];
        foreach ($schemaManager->listViews() as $dbalView) {
            $prototypeViews[$dbalView->getName()] = $dbalView->getSql();
        }

        return new Schema($connection, $prototypeTables, $prototypeViews);
    }
}