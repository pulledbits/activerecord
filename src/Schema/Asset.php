<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 15-12-16
 * Time: 15:19
 */

namespace ActiveRecord\Schema;

class Asset
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var \ActiveRecord\Schema
     */
    private $schema;

    public function __construct(string $identifier, \ActiveRecord\Schema $schema) {
        $this->identifier = $identifier;
        $this->schema = $schema;
    }

    public function executeRecordClassConfigurator(string $path, array $values) : \ActiveRecord\Record
    {
        $configurator = require $path . DIRECTORY_SEPARATOR . $this->identifier . '.php';
        return $configurator($this, $values);
    }

    public function select(array $columnIdentifiers, array $whereParameters)
    {
        return $this->schema->selectFrom($this->identifier, $columnIdentifiers, $whereParameters, function(\Closure $recordConfigurator) {
            return $recordConfigurator($this);
        });
    }

    public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters)
    {
        return $this->schema->selectFrom($tableIdentifier, $columnIdentifiers, $whereParameters, function(\Closure $recordConfigurator) use ($tableIdentifier) {
            return $recordConfigurator(new Asset($tableIdentifier, $this->schema));
        });
    }

    public function insert(array $values) {
        $this->schema->insertValues($this->identifier, $values);
        return $this->select(array_keys($values), $values);
    }

    public function update(array $setParameters, array $whereParameters) {
        $this->schema->updateWhere($this->identifier, $setParameters, $whereParameters);
        return $this->select(array_keys($setParameters), $whereParameters);
    }

    public function delete(array $whereParameters) {
        $records = $this->select(array_keys($whereParameters), $whereParameters);
        $this->schema->deleteFrom($this->identifier , $whereParameters);
        return $records;
    }
}