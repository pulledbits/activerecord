<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\Record;
use pulledbits\ActiveRecord\SQL\Connection;

final class Schema implements \pulledbits\ActiveRecord\Source\Schema
{
    private $schema;
    private $prototypeEntities;

    public function __construct(Connection $connection, \pulledbits\ActiveRecord\Schema $schema)
    {
        $this->schema = $schema;

        $tables = [];
        $fullTables = $schema->listTables();
        foreach ($fullTables->fetchAll() as $baseTable) {
            $tableIdentifier = array_shift($baseTable);

            $tables[$tableIdentifier] = new TableDescription([], [], []);

            $indexes = $schema->listIndexesForTable($tableIdentifier)->fetchAll();
            foreach ($indexes as $index) {
                if ($index['Key_name'] === 'PRIMARY') {
                    $tables[$tableIdentifier]->identifier[] = $index['Column_name'];
                }
            }

            $columns = $schema->listColumnsForTable($tableIdentifier)->fetchAll();
            foreach ($columns as $column) {
                if ($column['Extra'] === 'auto_increment') {
                    continue;
                } elseif ($column['Null'] === 'NO') {
                    $tables[$tableIdentifier]->requiredAttributeIdentifiers[] = $column['Field'];
                }
            }

            $foreignKeys = $schema->listForeignKeys($tableIdentifier)->fetchAll();
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

        $fullViews = $schema->listViews()->fetchAll();
        foreach ($fullViews as $fullView) {
            $viewIdentifier = $fullView['TABLE_NAME'];

            $underscorePosition = strpos($viewIdentifier, '_');
            if ($underscorePosition < 1) {
                continue;
            }
            $possibleEntityTypeIdentifier = substr($viewIdentifier, 0, $underscorePosition);
            if (array_key_exists($possibleEntityTypeIdentifier, $prototypeEntities) === false) {
                continue;
            }
            $prototypeEntities[$viewIdentifier] = $prototypeEntities[$possibleEntityTypeIdentifier];
        }

        $this->prototypeEntities = $prototypeEntities;
    }

    public function makeRecord(string $tableIdentifier) : Record
    {
        if (array_key_exists($tableIdentifier, $this->prototypeEntities) === false) {
            $tableDescription = new TableDescription();
        } else {
            $tableDescription = $this->prototypeEntities[$tableIdentifier];
        }
        return $this->schema->makeRecord($tableIdentifier, $tableDescription);
    }
}