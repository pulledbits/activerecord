<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:08
 */

namespace ActiveRecord\SQL;


use ActiveRecord\Entity;
use ActiveRecord\Record;

class Schema implements \ActiveRecord\Schema
{
    /**
     * @var \ActiveRecord\RecordFactory
     */
    private $recordFactory;

    /**
     * @var \PDO
     */
    private $connection;

    public function __construct(\ActiveRecord\RecordFactory $recordFactory, \PDO $connection) {
        $this->recordFactory = $recordFactory;
        $this->connection = $connection;
    }

    private function execute(string $query, array $namedParameters) : \PDOStatement
    {
        $statement = $this->connection->prepare($query);
        foreach ($namedParameters as $namedParameter => $value) {
            $statement->bindValue($namedParameter, $value, \PDO::PARAM_STR);
        }

        if ($statement->execute() === false) {
            trigger_error("Failed executing query `" . $query . "` (" . json_encode($namedParameters) . "): " . $statement->errorInfo()[2], E_USER_ERROR);
        }

        return $statement;
    }

    private function executeWhere(string $query, array $whereParameters) : \PDOStatement
    {
        $where = $this->makeWhereCondition($whereParameters);
        return $this->execute($query . $where[self::PP_SQL], $where[self::PP_PARAMS]);
    }

    const PP_COLUMN = 'column';
    const PP_VALUE = 'value';
    const PP_SQL = 'sql';
    const PP_PARAM = 'parameter';
    const PP_PARAMS = 'parameters';

    private function prepareParameters(array $parameters) {
        $preparedParameters = [];
        foreach ($parameters as $localColumn => $value) {
            $preparedParameters[] = [
                self::PP_COLUMN => $localColumn,
                self::PP_VALUE => $value,
                self::PP_PARAM => ":" . uniqid()
            ];
        }
        return $preparedParameters;
    }
    private function extract(string $type, array $preparedParameters) {
        return array_map(function(array $preparedParameters) use ($type) { return $preparedParameters[$type]; }, $preparedParameters);
    }
    private function extractParameters(array $preparedParameters) {
        return array_combine($this->extract(self::PP_PARAM, $preparedParameters), $this->extract(self::PP_VALUE, $preparedParameters));
    }
    private function extractParametersSQL(array $preparedParameters) {
        return array_map(function($preparedParameter) { return $preparedParameter[self::PP_COLUMN] . " = " . $preparedParameter[self::PP_PARAM]; }, $preparedParameters);
    }

    private function makeWhereCondition(array $whereParameters) {
        $preparedParameters = $this->prepareParameters($whereParameters);
        if (count($preparedParameters) === 0) {
            return [self::PP_SQL => '', self::PP_PARAMS => []];
        }
        return [
            self::PP_SQL => " WHERE " . join(" AND ", $this->extractParametersSQL($preparedParameters)),
            self::PP_PARAMS => $this->extractParameters($preparedParameters)
        ];
    }

    public function read(string $entityTypeIdentifier, array $columnIdentifiers, array $conditions) : array {
        if (count($columnIdentifiers) === 0) {
            $columnIdentifiers[] = '*';
        }
        $statement = $this->executeWhere("SELECT " . join(', ', $columnIdentifiers) . " FROM " . $entityTypeIdentifier, $conditions);

        return array_map(function(array $values) use ($entityTypeIdentifier) {
            return $this->recordFactory->makeRecord($this, $entityTypeIdentifier, $values);
        }, $statement->fetchAll(\PDO::FETCH_ASSOC));
    }
    public function readFirst(string $entityTypeIdentifier, array $columnIdentifiers, array $conditions) : \ActiveRecord\Record {
        $records = $this->read($entityTypeIdentifier, $columnIdentifiers, $conditions);
        if (count($records) === 0) {
            return new class($this->recordFactory->makeRecord($this, $entityTypeIdentifier, $conditions)) implements Record {

                /**
                 * @var \ActiveRecord\Record
                 */
                private $record;

                private $created;

                public function __construct(\ActiveRecord\Record $record)
                {
                    $this->record = $record;
                    $this->created = false;
                }

                /**
                 * @param string $property
                 */
                public function __get($property)
                {
                    return $this->record->__get($property);
                }

                public function read(string $entityTypeIdentifier, array $conditions): array
                {
                    return $this->record->read($entityTypeIdentifier, $conditions);
                }

                public function readFirst(string $entityTypeIdentifier, array $conditions): Entity
                {
                    return $this->record->readFirst($entityTypeIdentifier, $conditions);
                }

                /**
                 * @param string $property
                 * @param string $value
                 */
                public function __set($property, $value)
                {
                    if ($this->created === true) {
                        $this->record->__set($property, $value);
                    } elseif ($this->record->create() === 1) {
                        $this->created = true;
                    }
                }

                /**
                 */
                public function delete()
                {
                    return $this->record->delete();
                }

                public function create()
                {
                    return $this->record->create();
                }

                public function __call(string $method, array $arguments)
                {
                    return $this->record->__call($method, $arguments);
                }
            };
        }
        return $records[0];
    }

    public function update(string $tableIdentifier, array $setParameters, array $whereParameters) : int {
        $preparedParameters = $this->prepareParameters($setParameters);
        $query = "UPDATE " . $tableIdentifier . " SET " . join(", ", $this->extractParametersSQL($preparedParameters));
        $where = $this->makeWhereCondition($whereParameters);
        $statement = $this->execute($query . $where[self::PP_SQL], array_merge($this->extractParameters($preparedParameters), $where[self::PP_PARAMS]));
        return $statement->rowCount();
    }

    public function create(string $tableIdentifier, array $values) : int {
        $preparedParameters = $this->prepareParameters($values);
        $statement = $this->execute("INSERT INTO " . $tableIdentifier . " (" . join(', ', $this->extract(Schema::PP_COLUMN, $preparedParameters)) . ") VALUES (" . join(', ', $this->extract(Schema::PP_PARAM, $preparedParameters)) . ")", $this->extractParameters($preparedParameters));
        return $statement->rowCount();
    }

    public function delete(string $tableIdentifier, array $whereParameters) : int {
        $statement = $this->executeWhere("DELETE FROM " . $tableIdentifier , $whereParameters);
        return $statement->rowCount();
    }
}