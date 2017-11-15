<?php

namespace pulledbits\ActiveRecord\Test {
    /*
     * test specific bootstrapper
     */

    use PDO;

    $applicationBootstrap = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

    function createMockSchema(array $tables)
    {
        return new \pulledbits\ActiveRecord\SQL\Meta\Schema(new class($tables) extends \Doctrine\DBAL\Schema\MySqlSchemaManager
        {

            private $tables;
            private $views;

            public function __construct(array $tables)
            {
                $this->tables = $this->views = [];
                foreach ($tables as $tableIdentifier => $columns) {
                    if (is_array($columns)) {
                        $this->tables[] = \pulledbits\ActiveRecord\Test\createMockTable($tableIdentifier, $columns);
                    } elseif (is_string($columns)) {
                        $this->views[] = \pulledbits\ActiveRecord\Test\createMockView($tableIdentifier, $columns);
                    }
                }
            }

            public function listTables()
            {
                return $this->tables;
            }

            public function listViews()
            {
                return $this->views;
            }
        });
    }

    function createMockTable(string $tableIdentifier, array $columns) : \Doctrine\DBAL\Schema\Table
    {
        return new class($tableIdentifier, $columns) extends \Doctrine\DBAL\Schema\Table
        {

            private $identifier;
            private $primaryKey;
            private $foreignKeys;
            private $columns;

            public function __construct(string $identifier, array $columns)
            {
                $this->identifier = $identifier;
                $this->columns = [];
                $this->primaryKey = [];
                $foreignKeys = [];
                foreach ($columns as $columnIdentifier => $column) {
                    $this->columns[$columnIdentifier] = new class($column['required'], $column['auto_increment']) extends \Doctrine\DBAL\Schema\Column
                    {
                        private $required;
                        private $auto_increment;

                        public function __construct(bool $required, bool $auto_increment)
                        {
                            $this->required = $required;
                            $this->auto_increment = $auto_increment;
                        }

                        public function getNotnull()
                        {
                            return $this->required;
                        }

                        public function getAutoincrement()
                        {
                            return $this->auto_increment;
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
                return $this->identifier;
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

    function createMockView(string $identifier, string $sql) : \Doctrine\DBAL\Schema\View
    {
        return new \Doctrine\DBAL\Schema\View($identifier, $sql);
    }

    function createMockPDOStatement($results) {
        if (is_array($results)) {
            return createMockPDOStatementFetchAll($results);
        } elseif (is_int($results)) {
            return createMockPDOStatementRowCount($results);
        } elseif ($results === false) {
            return createMockPDOStatementFail($results);
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

            public function fetchAll($how = \PDO::ATTR_DEFAULT_FETCH_MODE, $class_name = NULL, $ctor_args = NULL)
            {
                if ($how === \PDO::ATTR_DEFAULT_FETCH_MODE) {
                    $how = \PDO::FETCH_ASSOC;
                }

                if ($how === \PDO::FETCH_ASSOC) {
                    return $this->results;
                }
            }

            public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
            {
                if ($fetch_style === \PDO::FETCH_ASSOC) {
                    return next($this->results);
                }
            }

            public function execute($bound_input_params = NULL)
            {
                return true;
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

            public function execute($bound_input_params = NULL)
            {
                return true;
            }
        };
    }

    function createMockPDOStatementFail(bool $results) {
        return new class($results) extends \PDOStatement
        {
            private $results;

            public function __construct(bool $results)
            {
                $this->results = $results;
            }

            public function rowCount()
            {
                return 0;
            }

            public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
            {
                return $this->results;
            }

            public function execute($bound_input_params = NULL)
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

            public function setAttribute ($attribute, $value) {
            }
            public function getAttribute($attribute) {
                switch ($attribute) {
                    case \PDO::ATTR_DRIVER_NAME:
                        return 'mysql';
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

                $tables = [];
                foreach ($this->queries as $query => $results) {
                    if (preg_match('/FROM (?<table>\w+)/', $query, $matches) === 1) {
                        $fullTables[] = [$matches['table'], 'BASE_TABLE'];
                    }
                }

                $this->queries['/SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'/'] = $fullTables;
                $this->queries['/SELECT COLUMN_NAME AS Field, COLUMN_TYPE AS Type, IS_NULLABLE AS `Null`, COLUMN_KEY AS `Key`, COLUMN_DEFAULT AS `Default`, EXTRA AS Extra, COLUMN_COMMENT AS Comment, CHARACTER_SET_NAME AS CharacterSet, COLLATION_NAME AS Collation FROM information_schema\.COLUMNS WHERE TABLE_SCHEMA = DATABASE\(\) AND TABLE_NAME = \'\w+\'/'] = [];
                $this->queries['/SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` \/\**!50116 , c.update_rule, c.delete_rule \*\/ FROM information_schema.key_column_usage k \/\**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'\w+\' \*\/ WHERE k.table_name = \'\w+\' AND k.table_schema = \'\' \/\**!50116 AND c.constraint_schema = \'\' \*\/ AND k.`REFERENCED_COLUMN_NAME` is not NULL/'] = [];
                $this->queries['/SHOW INDEX FROM \w+/'] = [];
                $this->queries['/SELECT \* FROM information_schema\.VIEWS WHERE TABLE_SCHEMA = \'\'/'] = [];
                $this->queries['/SELECT DATABASE()/'] = [];
            }

            public function query($statement, $mode = \PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = array())
            {
                return $this->prepare($statement);
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

            public function setAttribute($attribute, $value) {
            }
            public function getAttribute($attribute) {
                switch ($attribute) {
                    case \PDO::ATTR_DRIVER_NAME:
                        return 'mysql';
                }
            }
        };
    }
}