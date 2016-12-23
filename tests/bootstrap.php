<?php
namespace ActiveRecord\Test;
/* 
 * test specific bootstrapper
 */
$applicationBootstrap = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';
$applicationBootstrap();


function createMockSchema(array $tables) {
    return new Schema(new class($tables) extends \Doctrine\DBAL\Schema\MySqlSchemaManager {

        private $tables;

        public function __construct(array $tables) {
            foreach ($tables as $tableIdentifier => $columns) {
                $this->tables[] = \ActiveRecord\Test\createMockTable($tableIdentifier, $columns);
            }
        }

        public function listTables() {
            return [
                new class extends \Doctrine\DBAL\Schema\Table
                {
                    public function __construct() {}
                    public function getName() {
                        return 'MyTable';
                    }
                }
            ];
        }
    });
}

function createMockTable(string $tableIdentifier, array $columns) {
    return new class($tableIdentifier, $columns) extends \Doctrine\DBAL\Schema\Table {

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
                $this->columns[$columnIdentifier] = new class extends \Doctrine\DBAL\Schema\Column {public function __construct(){}};
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
        public function hasPrimaryKey() {
            return count($this->primaryKey) > 0;
        }
        public function getPrimaryKeyColumns() {
            return $this->primaryKey;
        }
        public function getForeignKeys()
        {
            return $this->foreignKeys;
        }
    };
}