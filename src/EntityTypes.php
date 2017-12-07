<?php


namespace pulledbits\ActiveRecord;


use pulledbits\ActiveRecord\SQL\EntityType;

class EntityTypes
{
    private $schema;
    private $entityIdentifiers;
    private $entityTypes;

    public function __construct(Schema $schema, Result $result)
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

    public function makeRecordType(string $tableIdentifier) : EntityType
    {
        if (array_key_exists($tableIdentifier, $this->entityIdentifiers) === false) {
            return new EntityType($this->schema, $tableIdentifier);
        } elseif (array_key_exists($tableIdentifier, $this->entityTypes) === false) {
            $this->entityTypes[$tableIdentifier] = new EntityType($this->schema, $tableIdentifier);
        }

        if ($this->entityIdentifiers[$tableIdentifier] === 'VIEW') {
            $underscorePosition = strpos($tableIdentifier, '_');
            if ($underscorePosition > 0) {
                $possibleEntityTypeIdentifier = substr($tableIdentifier, 0, $underscorePosition);
                $this->entityTypes[$tableIdentifier] = $this->makeRecordType($possibleEntityTypeIdentifier);
                return $this->entityTypes[$tableIdentifier];
            }
        }

        return $this->entityTypes[$tableIdentifier];
    }
}