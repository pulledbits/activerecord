<?php

namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\RecordTypes;

class EntityTypes implements RecordTypes
{
    private $schema;
    private $entityIdentifiers;
    private $entityTypes;

    public function __construct(Schema $schema, Query\Result $result)
    {
        $this->schema = $schema;
        $this->entityTypes = [];

        $this->entityIdentifiers = [];
        foreach ($result->fetchAll() as $baseTable) {
            $tableIdentifier = array_shift($baseTable);
            switch ($baseTable['Table_type']) {
                case 'BASE_TABLE':
                    $this->entityIdentifiers[$tableIdentifier] = 'BASE_TABLE';
                    break;
                case 'VIEW':
                    $this->entityIdentifiers[$tableIdentifier] = 'VIEW';
                    break;
            }
        }
    }

    public function makeRecordType(string $recordTypeIdentifier): EntityType
    {
        if (array_key_exists($recordTypeIdentifier, $this->entityIdentifiers) === false) {
            return new EntityType($this->schema, $recordTypeIdentifier);
        } elseif (array_key_exists($recordTypeIdentifier, $this->entityTypes) === false) {
            $this->entityTypes[$recordTypeIdentifier] = new EntityType($this->schema, $recordTypeIdentifier);
        }

        if ($this->entityIdentifiers[$recordTypeIdentifier] === 'VIEW') {
            $underscorePosition = strpos($recordTypeIdentifier, '_');
            if ($underscorePosition > 0) {
                $possibleEntityTypeIdentifier = substr($recordTypeIdentifier, 0, $underscorePosition);
                $this->entityTypes[$recordTypeIdentifier] = $this->makeRecordType($possibleEntityTypeIdentifier);
            }
        }

        return $this->entityTypes[$recordTypeIdentifier];
    }
}