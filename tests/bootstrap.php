<?php

namespace pulledbits\ActiveRecord\Test {
    /*
     * test specific bootstrapper
     */

    use PDO;
    use Psr\Http\Message\StreamInterface;
    use pulledbits\ActiveRecord\Result;

    require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

    function createMockSchemaManager(array $tables) {
        return new class($tables) extends \Doctrine\DBAL\Schema\MySqlSchemaManager
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
        };
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
            private $schema;

            public function __construct(array $queries)
            {
                $fullTables = [];
                $this->schema = '';
                foreach ($queries as $query => $results) {
                    if (preg_match('/(INTO|FROM)\s+((?<schema>\w+)\.)?(?<table>\w+)/', $query, $matches) === 1) {
                        $fullTables[$matches['schema']][] = ['Table_in_' . $matches['schema'] => $matches['table'], 'Table_type' => 'BASE_TABLE'];
                    }
                }

                $this->queries = [];
                foreach ($fullTables as $schemaIdentifier => $tables) {
                    $this->schema = $schemaIdentifier;
                    $this->defineTables($tables);
                    foreach ($tables as $table) {
                        $tableIdentifier = $table['Table_in_' . $schemaIdentifier];
                        $this->defineColumns($tableIdentifier, []);
                        $this->defineConstraints($tableIdentifier, []);
                        $this->defineIndexes($tableIdentifier, []);
                    }
                }
                $this->queries['/SELECT DATABASE()/'] = [];


                $this->queries = array_merge($this->queries, $queries);
            }

            public function defineSchema(string $schemaIdentifier) : void {
                $this->schema = $schemaIdentifier;
            }

            public function defineTables(array $tableResults) {
                $this->queries['/SHOW FULL TABLES IN ' . $this->schema . '/'] = $tableResults;
                foreach ($tableResults as $tableResult) {
                    $tableIdentifier = $tableResult['Table_in_' . $this->schema];
                    $this->defineColumns($tableIdentifier, []);
                    $this->defineConstraints($tableIdentifier, []);
                    $this->defineIndexes($tableIdentifier, []);
                }
            }

            public function defineColumns(string $tableIdentifier, array $columnResults) {
                $this->queries['/SHOW FULL COLUMNS IN ' . $this->schema . '.' . $tableIdentifier . '/'] = $columnResults;

            }
            public function defineConstraints(string $tableIdentifier, array $constraintResults) {
                $this->queries['/\(SELECT DISTINCT k\.`CONSTRAINT_NAME`, `k`\.`TABLE_NAME`, k\.`COLUMN_NAME`, k\.`REFERENCED_TABLE_NAME`, k\.`REFERENCED_COLUMN_NAME` \/\*\*\!50116 , c\.update_rule, c\.delete_rule \*\/ FROM information_schema\.key_column_usage k \/\*\*\!50116 INNER JOIN information_schema\.referential_constraints c ON   c\.constraint_name = k\.constraint_name AND   c\.table_name = \'' . $tableIdentifier . '\' \*\/ WHERE k\.table_name = \'' . $tableIdentifier . '\' AND k\.table_schema = \'' . $this->schema . '\' \/\*\*\!50116 AND c\.constraint_schema = \'' . $this->schema . '\' \*\/ AND k\.`REFERENCED_COLUMN_NAME` is not NULL\) UNION ALL \(SELECT DISTINCT k\.`CONSTRAINT_NAME`, k\.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k\.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`\.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k\.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` \/\*\*!50116 , c\.update_rule, c\.delete_rule \*\/ FROM information_schema\.key_column_usage k \/\*\*\!50116 INNER JOIN information_schema\.referential_constraints c ON   c\.constraint_name = k\.constraint_name AND   c\.`REFERENCED_TABLE_NAME` = \'' . $tableIdentifier . '\' \*\/ WHERE k\.`REFERENCED_TABLE_NAME` = \'' . $tableIdentifier . '\' AND k\.table_schema = \'' . $this->schema . '\' \/\*\*\!50116 AND c\.constraint_schema = \'' . $this->schema . '\' \*\/ AND k\.`REFERENCED_COLUMN_NAME` is not NULL\)/'] = $constraintResults;
            }

            public function defineIndexes(string $tableIdentifier, array $indexResults) {
                $this->queries['/SHOW INDEX FROM ' . $this->schema . '.' . $tableIdentifier . '/'] = $indexResults;
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
                throw new \PHPUnit\Framework\AssertionFailedError('Unexpected query \'' . $query . '\'');
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

    function createMockResult(array $results) : Result {
        return new class($results) implements Result {

            private $results;

            public function __construct(array $results)
            {
                $this->results = $results;
            }

            public function fetchAll(): array
            {
                return $this->results;
            }

            public function count()
            {
                return count($this->results);
            }
        };
    }

    function createTableResult(string $schemaIdentifier, string $tableIdentifier) {
        return ['Table_in_' . $schemaIdentifier => $tableIdentifier, 'Table_type' => 'BASE_TABLE'];
    }
    function createViewResult(string $schemaIdentifier, string $tableIdentifier) {
        return ['Table_in_' . $schemaIdentifier => $tableIdentifier, 'Table_type' => 'VIEW'];
    }
    function createColumnResult(string $columnIdentifier, string $typeIdentifier, bool $nullable, bool $autoincrement = false) {
        return [
            'Field' => $columnIdentifier,
            'Type' => $typeIdentifier,
            'Null' => $nullable ? 'YES' : 'NO',
            'Key' => 'PRI',
            'Default' => '',
            'Extra' => $autoincrement ? 'auto_increment' : '',
            'Comment' => '',
            'CharacterSet' => '',
            'Collation' => ''
        ];
    }
    function createConstraintResult(string $identifier, string $localColumnIdentifier, string $referencedTableIdentifier, string $referencedColumnIdentifier) {
        return [
            'CONSTRAINT_NAME' => $identifier,
            'COLUMN_NAME' => $localColumnIdentifier,
            'REFERENCED_TABLE_NAME' => $referencedTableIdentifier,
            'REFERENCED_COLUMN_NAME' => $referencedColumnIdentifier
        ];
    }

    define('CONSTRAINT_KEY_UNIQUE', 'UNIQUE');
    define('CONSTRAINT_KEY_FOREIGN', 'FOREIGN');
    define('CONSTRAINT_KEY_PRIMARY', 'PRIMARY');
    function createIndexResult(string $tableIdentifier, string $keyIdentifier, string $columnIdentifier) {
        return [
            'Table' => $tableIdentifier,
            'Non_unique' => '0',
            'Key_name' => $keyIdentifier,
            'Seq_in_index' => '1',
            'Column_name' => $columnIdentifier,
            'Collation' => 'A',
            'Cardinality' => '1',
            'Sub_part' => null,
            'Packed' => null,
            'Null' => '',
            'Index_type' => 'BTREE',
            'Comment' => '',
            'Index_comment' => ''
        ];
    }

    function createMockStreamInterface() {
        return new class implements StreamInterface {

            private $stream;

            public function __construct()
            {
                $this->stream = fopen("php://memory", 'w+');
            }

            /**
             * Reads all data from the stream into a string, from the beginning to end.
             *
             * This method MUST attempt to seek to the beginning of the stream before
             * reading data and read the stream until the end is reached.
             *
             * Warning: This could attempt to load a large amount of data into memory.
             *
             * This method MUST NOT raise an exception in order to conform with PHP's
             * string casting operations.
             *
             * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
             * @return string
             */
            public function __toString()
            {
                $this->rewind();
                return stream_get_contents($this->stream);
            }

            /**
             * Closes the stream and any underlying resources.
             *
             * @return void
             */
            public function close()
            {
                fclose($this->stream);
            }

            /**
             * Separates any underlying resources from the stream.
             *
             * After the stream has been detached, the stream is in an unusable state.
             *
             * @return resource|null Underlying PHP stream, if any
             */
            public function detach()
            {
            }

            /**
             * Get the size of the stream if known.
             *
             * @return int|null Returns the size in bytes if known, or null if unknown.
             */
            public function getSize()
            {
                return null;
            }

            /**
             * Returns the current position of the file read/write pointer
             *
             * @return int Position of the file pointer
             * @throws \RuntimeException on error.
             */
            public function tell()
            {
                return ftell($this->stream);
            }

            /**
             * Returns true if the stream is at the end of the stream.
             *
             * @return bool
             */
            public function eof()
            {
                return feof($this->stream);
            }

            /**
             * Returns whether or not the stream is seekable.
             *
             * @return bool
             */
            public function isSeekable()
            {
                return true;
            }

            /**
             * Seek to a position in the stream.
             *
             * @link http://www.php.net/manual/en/function.fseek.php
             * @param int $offset Stream offset
             * @param int $whence Specifies how the cursor position will be calculated
             *     based on the seek offset. Valid values are identical to the built-in
             *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
             *     offset bytes SEEK_CUR: Set position to current location plus offset
             *     SEEK_END: Set position to end-of-stream plus offset.
             * @throws \RuntimeException on failure.
             */
            public function seek($offset, $whence = SEEK_SET)
            {
                fseek($this->stream, $offset, $whence);
            }

            /**
             * Seek to the beginning of the stream.
             *
             * If the stream is not seekable, this method will raise an exception;
             * otherwise, it will perform a seek(0).
             *
             * @see seek()
             * @link http://www.php.net/manual/en/function.fseek.php
             * @throws \RuntimeException on failure.
             */
            public function rewind()
            {
                $this->seek(0);
            }

            /**
             * Returns whether or not the stream is writable.
             *
             * @return bool
             */
            public function isWritable()
            {
                return true;
            }

            /**
             * Write data to the stream.
             *
             * @param string $string The string that is to be written.
             * @return int Returns the number of bytes written to the stream.
             * @throws \RuntimeException on failure.
             */
            public function write($string)
            {
                return fwrite($this->stream, $string);
            }

            /**
             * Returns whether or not the stream is readable.
             *
             * @return bool
             */
            public function isReadable()
            {
                return true;
            }

            /**
             * Read data from the stream.
             *
             * @param int $length Read up to $length bytes from the object and return
             *     them. Fewer than $length bytes may be returned if underlying stream
             *     call returns fewer bytes.
             * @return string Returns the data read from the stream, or an empty string
             *     if no bytes are available.
             * @throws \RuntimeException if an error occurs.
             */
            public function read($length)
            {
                return fread($this->stream, $length);
            }

            /**
             * Returns the remaining contents in a string
             *
             * @return string
             * @throws \RuntimeException if unable to read or an error occurs while
             *     reading.
             */
            public function getContents()
            {
                return stream_get_contents($this->stream);
            }

            /**
             * Get stream metadata as an associative array or retrieve a specific key.
             *
             * The keys returned are identical to the keys returned from PHP's
             * stream_get_meta_data() function.
             *
             * @link http://php.net/manual/en/function.stream-get-meta-data.php
             * @param string $key Specific metadata to retrieve.
             * @return array|mixed|null Returns an associative array if no key is
             *     provided. Returns a specific key value if a key is provided and the
             *     value is found, or null if the key is not found.
             */
            public function getMetadata($key = null)
            {
                return stream_get_meta_data($this->stream);
            }
        };
    }
}