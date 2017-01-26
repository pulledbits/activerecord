<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:08
 */

namespace ActiveRecord\SQL;


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
            $statement->bindParam($namedParameter, $value, \PDO::PARAM_STR);
        }
        $statement->execute();
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

    public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters) : array {
        $statement = $this->executeWhere("SELECT " . join(', ', $columnIdentifiers) . " FROM " . $tableIdentifier, $whereParameters);

        return array_map(function(array $values) use ($tableIdentifier) {
            return $this->recordFactory->makeRecord($this, $tableIdentifier, $values);
        }, $statement->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function updateWhere(string $tableIdentifier, array $setParameters, array $whereParameters) : int {
        $preparedParameters = $this->prepareParameters($setParameters);
        $query = "UPDATE " . $tableIdentifier . " SET " . join(", ", $this->extractParametersSQL($preparedParameters));
        $where = $this->makeWhereCondition($whereParameters);
        $statement = $this->execute($query . $where[self::PP_SQL], array_merge($this->extractParameters($preparedParameters), $where[self::PP_PARAMS]));
        return $statement->rowCount();
    }

    public function insertValues(string $tableIdentifier, array $values) : int {
        $preparedParameters = $this->prepareParameters($values);
        $statement = $this->execute("INSERT INTO " . $tableIdentifier . " (" . join(', ', $this->extract(Schema::PP_COLUMN, $preparedParameters)) . ") VALUES (" . join(', ', $this->extract(Schema::PP_PARAM, $preparedParameters)) . ")", $this->extractParameters($preparedParameters));
        return $statement->rowCount();
    }

    public function deleteFrom(string $tableIdentifier, array $whereParameters) : int {
        $statement = $this->executeWhere("DELETE FROM " . $tableIdentifier , $whereParameters);
        return $statement->rowCount();
    }
}