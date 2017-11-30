<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
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
        $tables = [];
        $fullTables = $connection->execute('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'', []);
        foreach ($fullTables->fetchAll() as $baseTable) {
            $dbalSchemaTable = $schemaManager->listTableDetails($baseTable[0]);

            $tables[$baseTable[0]] = new TableDescription([], [], []);

            if ($dbalSchemaTable->hasPrimaryKey()) {
                $tables[$baseTable[0]]->identifier = $dbalSchemaTable->getPrimaryKeyColumns();
            }

            foreach ($dbalSchemaTable->getColumns() as $columnIdentifier => $column) {
                if ($column->getAutoincrement()) {
                    continue;
                } elseif ($column->getNotnull()) {
                    $tables[$baseTable[0]]->requiredAttributeIdentifiers[] = $columnIdentifier;
                }
            }

            foreach ($dbalSchemaTable->getForeignKeys() as $foreignKeyIdentifier => $foreignKey) {
                $tables[$baseTable[0]]->references[join('', array_map('ucfirst', explode('_', $foreignKeyIdentifier)))] = TableDescription::makeReference($foreignKey->getForeignTableName(), array_combine($foreignKey->getForeignColumns(), $foreignKey->getLocalColumns()));
            }
        }
        $prototypeTables = $tables;
        foreach ($tables as $tableName => $recordClassDescription) {
            foreach ($recordClassDescription->references as $referenceIdentifier => $reference) {
                $prototypeTables[$reference['table']]->references[$referenceIdentifier] = TableDescription::makeReference($tableName, array_flip($reference['where']));
            }
        }

        $prototypeViews = [];
        foreach ($schemaManager->listViews() as $dbalView) {
            $prototypeViews[$dbalView->getName()] = $dbalView->getSql();
        }

        return new Schema($connection, $prototypeTables, $prototypeViews);
    }
}