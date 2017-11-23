<?php

namespace pulledbits\ActiveRecord\Test {
    /*
     * test specific bootstrapper
     */

    use PDO;
    use Psr\Http\Message\StreamInterface;
    use pulledbits\ActiveRecord\SQL\Connection;
    use pulledbits\ActiveRecord\SQL\Meta\SchemaFactory;

    $applicationBootstrap = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

    function createMockSchema(Connection $connection, array $tables)
    {
        $schemaManager = new class($tables) extends \Doctrine\DBAL\Schema\MySqlSchemaManager
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

        return SchemaFactory::makeFromSchemaManager($connection, $schemaManager);
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

                $fullTables = [];
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
                // TODO: Implement detach() method.
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