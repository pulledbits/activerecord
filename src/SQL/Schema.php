<?php

namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\Record;

/**
 * Class Schema
 * @package pulledbits\ActiveRecord\SQL
 */
final class Schema implements \pulledbits\ActiveRecord\Schema
{
    /**
     * @var \pulledbits\ActiveRecord\RecordFactory
     */
    private $recordFactory;

    /**
     * @var \PDO
     */
    private $connection;

    /**
     * Schema constructor.
     * @param \pulledbits\ActiveRecord\RecordFactory $recordFactory
     * @param \PDO $connection
     */
    public function __construct(\pulledbits\ActiveRecord\RecordFactory $recordFactory, \PDO $connection) {
        $this->recordFactory = $recordFactory;
        $this->connection = $connection;
    }

    /**
     * @param string $query
     * @param array $namedParameters
     * @return \PDOStatement
     */
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

    /**
     * @param string $query
     * @param array $whereParameters
     * @return \PDOStatement
     */
    private function executeWhere(string $query, array $whereParameters) : \PDOStatement
    {
        $where = $this->makeWhereCondition($whereParameters);
        return $this->execute($query . $where[self::PP_SQL], $where[self::PP_PARAMS]);
    }

    /**
     *
     */
    const PP_COLUMN = 'column';
    /**
     *
     */
    const PP_VALUE = 'value';
    /**
     *
     */
    const PP_SQL = 'sql';
    /**
     *
     */
    const PP_PARAM = 'parameter';
    /**
     *
     */
    const PP_PARAMS = 'parameters';

    /**
     * @param array $parameters
     * @return array
     */
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

    /**
     * @param string $type
     * @param array $preparedParameters
     * @return array
     */
    private function extract(string $type, array $preparedParameters) {
        return array_map(function(array $preparedParameters) use ($type) { return $preparedParameters[$type]; }, $preparedParameters);
    }

    /**
     * @param array $preparedParameters
     * @return array
     */
    private function extractParameters(array $preparedParameters) {
        return array_combine($this->extract(self::PP_PARAM, $preparedParameters), $this->extract(self::PP_VALUE, $preparedParameters));
    }

    /**
     * @param array $preparedParameters
     * @return array
     */
    private function extractParametersSQL(array $preparedParameters) {
        return array_map(function($preparedParameter) { return $preparedParameter[self::PP_COLUMN] . " = " . $preparedParameter[self::PP_PARAM]; }, $preparedParameters);
    }

    /**
     * @param array $whereParameters
     * @return array
     */
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

    /**
     * @param $entityTypeIdentifier
     * @param array $values
     * @return \pulledbits\ActiveRecord\Entity
     */
    private function makeRecord($entityTypeIdentifier, array $values) {
        $record = $this->recordFactory->makeRecord($this, $entityTypeIdentifier);
        $record->contains($values);
        return $record;
    }

    /**
     * @param string $entityTypeIdentifier
     * @param array $attributeIdentifiers
     * @param array $conditions
     * @return array
     */
    public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions) : array {
        if (count($attributeIdentifiers) === 0) {
            $attributeIdentifiers[] = '*';
        }
        $statement = $this->executeWhere("SELECT " . join(', ', $attributeIdentifiers) . " FROM " . $entityTypeIdentifier, $conditions);

        return array_map(function(array $values) use ($entityTypeIdentifier) {
            return $this->makeRecord($entityTypeIdentifier, $values);
        }, $statement->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * @param string $entityTypeIdentifier
     * @param array $values
     * @return Record
     */
    public function initializeRecord(string $entityTypeIdentifier, array $values) : Record {
        $record = $this->makeRecord($entityTypeIdentifier, $values);
        return new Record\Fresh($record);
    }

    /**
     * @param string $entityTypeIdentifier
     * @param array $attributeIdentifiers
     * @param array $conditions
     * @return Record
     */
    public function readFirst(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions) : \pulledbits\ActiveRecord\Record {
        $records = $this->read($entityTypeIdentifier, $attributeIdentifiers, $conditions);
        if (count($records) === 0) {
            return $this->initializeRecord($entityTypeIdentifier, $conditions);
        }
        return $records[0];
    }

    /**
     * @param string $tableIdentifier
     * @param array $values
     * @param array $conditions
     * @return int
     */
    public function update(string $tableIdentifier, array $values, array $conditions) : int {
        $preparedParameters = $this->prepareParameters($values);
        $query = "UPDATE " . $tableIdentifier . " SET " . join(", ", $this->extractParametersSQL($preparedParameters));
        $where = $this->makeWhereCondition($conditions);
        $statement = $this->execute($query . $where[self::PP_SQL], array_merge($this->extractParameters($preparedParameters), $where[self::PP_PARAMS]));
        return $statement->rowCount();
    }

    /**
     * @param string $tableIdentifier
     * @param array $values
     * @return int
     */
    public function create(string $tableIdentifier, array $values) : int {
        $preparedParameters = $this->prepareParameters($values);
        $statement = $this->execute("INSERT INTO " . $tableIdentifier . " (" . join(', ', $this->extract(Schema::PP_COLUMN, $preparedParameters)) . ") VALUES (" . join(', ', $this->extract(Schema::PP_PARAM, $preparedParameters)) . ")", $this->extractParameters($preparedParameters));
        return $statement->rowCount();
    }

    /**
     * @param string $tableIdentifier
     * @param array $conditions
     * @return int
     */
    public function delete(string $tableIdentifier, array $conditions) : int {
        $statement = $this->executeWhere("DELETE FROM " . $tableIdentifier , $conditions);
        return $statement->rowCount();
    }
}