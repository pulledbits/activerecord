<?php
namespace pulledbits\ActiveRecord\SQL\Meta;
use pulledbits\ActiveRecord\SQL\Connection;

class SchemaFactory
{
    public static function makeFromConnection(Connection $connection): Schema
    {
        return new Schema($connection, $connection->schema());
    }
}