<?php
namespace pulledbits\ActiveRecord\SQL\Meta;
use pulledbits\ActiveRecord\SQL\Connection;

class SchemaFactory
{
    public static function makeFromConnection(Connection $connection): Schema
    {
        $tables = [];
        $fullTables = $connection->execute('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'', []);
        foreach ($fullTables->fetchAll() as $baseTable) {
            $tableIdentifier = array_shift($baseTable);

            $tables[$tableIdentifier] = new TableDescription([], [], []);

            $indexes = $connection->execute('SHOW INDEX FROM ' . $tableIdentifier, [])->fetchAll();
            foreach ($indexes as $index) {
                if ($index['Key_name'] === 'PRIMARY') {
                    $tables[$tableIdentifier]->identifier[] = $index['Column_name'];
                }
            }

            $columns = $connection->execute('SHOW FULL COLUMNS IN ' . $tableIdentifier, [])->fetchAll();
            foreach ($columns as $column) {
                if ($column['Extra'] === 'auto_increment') {
                    continue;
                } elseif ($column['Null'] === 'NO') {
                    $tables[$tableIdentifier]->requiredAttributeIdentifiers[] = $column['Field'];
                }
            }

            $foreignKeys = $connection->execute('SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'' . $tableIdentifier . '\' */ WHERE k.table_name = \'' . $tableIdentifier . '\' AND k.table_schema = DATABASE() /**!50116 AND c.constraint_schema = DATABASE() */ AND k.`REFERENCED_COLUMN_NAME` is not NULL', [])->fetchAll();
            foreach ($foreignKeys as $foreignKey) {
                $tables[$tableIdentifier]->addForeignKeyConstraint($foreignKey['CONSTRAINT_NAME'], $foreignKey['COLUMN_NAME'], $foreignKey['REFERENCED_TABLE_NAME'], $foreignKey['REFERENCED_COLUMN_NAME']);
            }
        }
        $prototypeEntities = $tables;
        foreach ($tables as $tableName => $recordClassDescription) {
            foreach ($recordClassDescription->references as $referenceIdentifier => $reference) {
                foreach ($reference['where'] as $localColumnIdentifier => $referencedColumnIdentifier) {
                    $prototypeEntities[$reference['table']]->addForeignKeyConstraint($referenceIdentifier, $localColumnIdentifier, $tableName, $referencedColumnIdentifier);
                }
            }
        }

        $fullViews = $connection->execute('SELECT TABLE_NAME, VIEW_DEFINITION FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE()', [])->fetchAll();
        foreach ($fullViews as $fullView) {
            $viewIdentifier = $fullView['TABLE_NAME'];

            $underscorePosition = strpos($viewIdentifier, '_');
            if ($underscorePosition < 1) {
                $prototypeEntities[$viewIdentifier] = new TableDescription();
                continue;
            }
            $possibleEntityTypeIdentifier = substr($viewIdentifier, 0, $underscorePosition);
            if (array_key_exists($possibleEntityTypeIdentifier, $prototypeEntities) || array_key_exists($possibleEntityTypeIdentifier, $prototypeViews) === false) {
                $prototypeEntities[$viewIdentifier] = new TableDescription();
                continue;
            }

            $prototypeEntities[$viewIdentifier] = $prototypeEntities[$possibleEntityTypeIdentifier];
        }

        return new Schema($connection->schema(), $prototypeEntities);
    }
}