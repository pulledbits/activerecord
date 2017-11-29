<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

final class Table implements \pulledbits\ActiveRecord\Source\Table
{
    public function describe(\Doctrine\DBAL\Schema\Table $dbalSchemaTable) : TableDescription {
        return TableDescription::makeFromDBALTable($dbalSchemaTable);
    }

    public function makeReference(string $entityTypeIdentifier, array $conditions): array
    {
        return TableDescription::makeReference($entityTypeIdentifier, $conditions);
    }
}