<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

class SchemaFactory
{
    public static function makeFromConnection(\PDO $connection, \pulledbits\ActiveRecord\SQL\Schema $schema): Schema
    {
        $tables = [];
        $fullTables = $connection->query('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'');
        foreach ($fullTables->fetchAll() as $baseTable) {
            $tableIdentifier = array_shift($baseTable);

            $tables[$tableIdentifier] = new TableDescription([], [], []);

            $indexes = $connection->query('SHOW INDEX FROM ' . $tableIdentifier)->fetchAll();
            foreach ($indexes as $index) {
                if ($index['Key_name'] === 'PRIMARY') {
                    $tables[$tableIdentifier]->identifier[] = $index['Column_name'];
                }
            }

            $columns = $connection->query('SHOW FULL COLUMNS IN ' . $tableIdentifier)->fetchAll();
            foreach ($columns as $column) {
                if ($column['Extra'] === 'auto_increment') {
                    continue;
                } elseif ($column['Null'] === 'NO') {
                    $tables[$tableIdentifier]->requiredAttributeIdentifiers[] = $column['Field'];
                }
            }

            $foreignKeys = $connection->query('SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'' . $tableIdentifier . '\' */ WHERE k.table_name = \'' . $tableIdentifier . '\' AND k.table_schema = DATABASE() /**!50116 AND c.constraint_schema = DATABASE() */ AND k.`REFERENCED_COLUMN_NAME` is not NULL')->fetchAll();
            foreach ($foreignKeys as $foreignKey) {
                $tables[$tableIdentifier]->addForeignKeyConstraint($foreignKey['CONSTRAINT_NAME'], $foreignKey['COLUMN_NAME'], $foreignKey['REFERENCED_TABLE_NAME'], $foreignKey['REFERENCED_COLUMN_NAME']);
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
        $fullViews = $connection->query('SELECT TABLE_NAME, VIEW_DEFINITION FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE()')->fetchAll();
        foreach ($fullViews as $fullView) {
            $prototypeViews[$fullView['TABLE_NAME']] = $fullView['VIEW_DEFINITION'];
        }

        return new Schema($schema, $prototypeTables, $prototypeViews);
    }
}