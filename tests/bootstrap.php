<?php
namespace Test\Record {
    class activiteit implements \ActiveRecord\WritableRecord
    {

        /**
         */
        public function delete()
        {
        }

        public function __set($property, $value)
        {
        }

        public function __get($property)
        {
            return 'newName';
        }
    }

    class thema implements \ActiveRecord\ReadableRecord
    {

        public function __get($property)
        {
            return 'newName';
        }
    }
}

namespace ActiveRecord\Test {
    /*
     * test specific bootstrapper
     */
    $applicationBootstrap = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';
    $applicationBootstrap();

    function createMockSchema(array $tables)
    {
        return new \ActiveRecord\Source\Schema(new class($tables) extends \Doctrine\DBAL\Schema\MySqlSchemaManager
        {

            private $tables;

            public function __construct(array $tables)
            {
                foreach ($tables as $tableIdentifier => $columns) {
                    $this->tables[] = \ActiveRecord\Test\createMockTable($tableIdentifier, $columns);
                }
            }

            public function listTables()
            {
                return [
                    new class extends \Doctrine\DBAL\Schema\Table
                    {
                        public function __construct()
                        {
                        }

                        public function getName()
                        {
                            return 'MyTable';
                        }
                    }
                ];
            }
        });
    }

    function createMockTable(string $tableIdentifier, array $columns) : \Doctrine\DBAL\Schema\Table
    {
        return new class($tableIdentifier, $columns) extends \Doctrine\DBAL\Schema\Table
        {

            private $tableIdentifier;
            private $primaryKey;
            private $foreignKeys;
            private $columns;

            public function __construct(string $tableIdentifier, array $columns)
            {
                $this->tableIdentifier = $tableIdentifier;
                $this->columns = [];
                $this->primaryKey = [];
                $foreignKeys = [];
                foreach ($columns as $columnIdentifier => $column) {
                    $this->columns[$columnIdentifier] = new class extends \Doctrine\DBAL\Schema\Column
                    {
                        public function __construct()
                        {
                        }
                    };
                    if ($column['primaryKey']) {
                        $this->primaryKey[] = $columnIdentifier;
                    }
                    if (array_key_exists('references', $column)) {
                        foreach ($column['references'] as $foreignKeyIdentifier => $foreignKey) {
                            if (array_key_exists($foreignKeyIdentifier, $foreignKeys) === false) {
                                $foreignKeys[$foreignKeyIdentifier] = [
                                    'table' => $foreignKey[0],
                                    'columns' => [],
                                    'foreignColumns' => []
                                ];
                            }

                            $foreignKeys[$foreignKeyIdentifier]['columns'][] = $columnIdentifier;
                            $foreignKeys[$foreignKeyIdentifier]['foreignColumns'][] = $foreignKey[1];
                        }
                    }
                }

                $this->foreignKeys = [];
                foreach ($foreignKeys as $foreignKeyIdentifier => $foreignKey) {
                    $this->foreignKeys[$foreignKeyIdentifier] = new \Doctrine\DBAL\Schema\ForeignKeyConstraint($foreignKey['columns'], $foreignKey['table'], $foreignKey['foreignColumns'], $foreignKeyIdentifier);
                }

            }

            public function getName()
            {
                return $this->tableIdentifier;
            }

            public function getColumns()
            {
                return $this->columns;
            }

            public function hasPrimaryKey()
            {
                return count($this->primaryKey) > 0;
            }

            public function getPrimaryKeyColumns()
            {
                return $this->primaryKey;
            }

            public function getForeignKeys()
            {
                return $this->foreignKeys;
            }
        };
    }

    function createMockPDOStatement($results) {
        if (is_array($results)) {
            return createMockPDOStatementFetchAll($results);
        } elseif (is_int($results)) {
            return createMockPDOStatementRowCount($results);
        }
        return;
    }

    function createMockPDOStatementFetchAll(array $results) {
        return new class($results) extends \PDOStatement
        {
            private $results;

            public function __construct(array $results)
            {
                $this->results = $results;
            }

            public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
            {
                if ($how === \PDO::FETCH_ASSOC) {
                    return $this->results;
                }
            }
        };
    }

    function createMockPDOStatementRowCount(int $results) {
        return new class($results) extends \PDOStatement
        {
            private $results;

            public function __construct(int $results)
            {
                $this->results = $results;
            }

            public function rowCount()
            {
                return $this->results;
            }
        };
    }


    function createMockPDO(string $query, array $results)
    {
        return new class($query, $results) extends \PDO
        {

            private $query;
            private $results;

            public function __construct(string $query, array $results)
            {
                $this->query = $query;
                $this->results = $results;
            }

            public function prepare($query, $options = null)
            {
                if (preg_match($this->query, $query, $match) === 1) {
                    return createMockPDOStatement($this->results);
                }
            }
        };
    }

    function createMockPDOMultiple(array $queries): \PDO
    {
        return new class($queries) extends \PDO
        {

            private $queries;

            public function __construct(array $queries)
            {
                $this->queries = $queries;
            }

            public function prepare($query, $options = null)
            {
                foreach ($this->queries as $expectedQuery => $results) {
                    if (preg_match($expectedQuery, $query, $match) === 1) {
                        return createMockPDOStatement($results);
                    }
                }
                throw new \PHPUnit_Framework_AssertionFailedError('Unexpected query \'' . $query . '\'');
            }
        };
    }
}