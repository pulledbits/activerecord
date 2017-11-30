<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
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
            $tables[$baseTable[0]] = new TableDescription([], [], []);

            $indexes = $connection->execute('SHOW INDEX FROM ' . $baseTable[0], [])->fetchAll();
            foreach ($indexes as $index) {
                if ($index['Key_name'] === 'PRIMARY') {
                    $tables[$baseTable[0]]->identifier[] = $index['Column_name'];
                }
            }

            $columns = $connection->execute('SHOW FULL COLUMNS IN ' . $baseTable[0], [])->fetchAll();
            foreach ($columns as $column) {
                if ($column['Extra'] === 'auto_increment') {
                    continue;
                } elseif ($column['Null'] === 'NO') {
                    $tables[$baseTable[0]]->requiredAttributeIdentifiers[] = $column['Field'];
                }
            }

            $foreignKeys = $connection->execute('SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'' . $baseTable[0] . '\' */ WHERE k.table_name = \'' . $baseTable[0] . '\' AND k.table_schema = \'\' /**!50116 AND c.constraint_schema = \'\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL', [])->fetchAll();
            foreach ($foreignKeys as $foreignKey) {
                $tables[$baseTable[0]]->addForeignKeyConstraint($foreignKey['CONSTRAINT_NAME'], $foreignKey['COLUMN_NAME'], $foreignKey['REFERENCED_TABLE_NAME'], $foreignKey['REFERENCED_COLUMN_NAME']);
            }
        }
        $prototypeTables = $tables;
        foreach ($tables as $tableName => $recordClassDescription) {
            foreach ($recordClassDescription->references as $referenceIdentifier => $reference) {
                foreach ($reference['where'] as $localColumnIdentifier => $referencedColumnIdentifier) {
                    $prototypeTables[$reference['table']]->addForeignKeyConstraint($referenceIdentifier, $localColumnIdentifier, $tableName, $referencedColumnIdentifier);
                }
            }
        }

        $prototypeViews = [];
        $fullViews = $connection->execute('SELECT * FROM information_schema.VIEWS WHERE TABLE_SCHEMA = \'\'', [])->fetchAll();
        foreach ($fullViews as $fullView) {
            $prototypeViews[$fullView['TABLE_NAME']] = $fullView['VIEW_DEFINITION'];
        }

        return new Schema($connection, $prototypeTables, $prototypeViews);
    }
}