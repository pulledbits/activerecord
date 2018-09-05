<?php

namespace pulledbits\ActiveRecord\SQL\MySQL;

use pulledbits\ActiveRecord\RecordTypes;

class Tables implements RecordTypes
{
    private $schema;
    private $tableIdentifiers;
    private $tables;

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
        $this->tables = [];

        $this->tableIdentifiers = [];
        foreach ($schema->listEntities()->fetchAll() as $baseTable) {
            $tableIdentifier = array_shift($baseTable);
            switch ($baseTable['Table_type']) {
                case 'BASE_TABLE':
                    $this->tableIdentifiers[$tableIdentifier] = 'BASE_TABLE';
                    break;
                case 'VIEW':
                    $this->tableIdentifiers[$tableIdentifier] = 'VIEW';
                    break;
            }
        }
    }

    public function makeEntityType(string $recordTypeIdentifier): \pulledbits\ActiveRecord\EntityType
    {
        if (array_key_exists($recordTypeIdentifier, $this->tableIdentifiers) === false) {
            return new Table($this->schema, $recordTypeIdentifier);
        } elseif (array_key_exists($recordTypeIdentifier, $this->tables) === false) {
            $this->tables[$recordTypeIdentifier] = new Table($this->schema, $recordTypeIdentifier);
        }

        if ($this->tableIdentifiers[$recordTypeIdentifier] === 'VIEW') {
            $underscorePosition = strpos($recordTypeIdentifier, '_');
            if ($underscorePosition > 0) {
                $possibleEntityTypeIdentifier = substr($recordTypeIdentifier, 0, $underscorePosition);
                $this->tables[$recordTypeIdentifier] = $this->makeEntityType($possibleEntityTypeIdentifier);
            }
        }

        return $this->tables[$recordTypeIdentifier];
    }
}