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

        $prototypeEntities = $schema->listTables();

        $fullViews = $schema->listViews()->fetchAll();
        foreach ($fullViews as $fullView) {
            $viewIdentifier = $fullView['TABLE_NAME'];
            $underscorePosition = strpos($viewIdentifier, '_');
            if ($underscorePosition < 1) {
                continue;
            }
            $possibleEntityTypeIdentifier = substr($viewIdentifier, 0, $underscorePosition);
            if (isset($prototypeEntities[$possibleEntityTypeIdentifier]) === false) {
                continue;
            }
            $prototypeEntities[$viewIdentifier] = $prototypeEntities[$possibleEntityTypeIdentifier];
        }

        $this->prototypeEntities = $prototypeEntities;
    }

    public function makeRecord(string $tableIdentifier) : Record
    {
        if (isset($this->prototypeEntities[$tableIdentifier]) === false) {
            $tableDescription = new TableDescription();
        } else {
            $tableDescription = $this->prototypeEntities[$tableIdentifier];
        }
        return $this->schema->makeRecord($tableIdentifier, $tableDescription);
    }
}