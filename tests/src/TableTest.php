<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 15-12-16
 * Time: 15:16
 */
namespace Test\Record {
    class activiteit implements \ActiveRecord\Record {

        /**
         */
        public function delete()
        {
            // TODO: Implement delete() method.
        }

        /**
         */
        public function fetchAll()
        {
            // TODO: Implement fetchAll() method.
        }
    }
}

namespace ActiveRecord {


    use PDO;

    class TableTest extends \PHPUnit_Framework_TestCase
    {

        public function testSelect_When_NoWhereParametersSupplied_Expect_FiveRecords()
        {
            $schema = new Table(new Schema('\Test\Record', new class extends \PDO
            {
                public function __construct()
                {
                }

                public function prepare($query, $options = null)
                {
                    if ($query === 'SELECT id AS _id, name AS _name FROM activiteit') {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }

                            public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
                            {
                                if ($how === \PDO::FETCH_CLASS && $class_name === '\Test\Record\activiteit') {
                                    return [
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                    ];
                                }
                            }
                        };
                    }
                }
            }));

            $this->assertCount(5, $schema->select('activiteit', ['id', 'name'], []));
        }

        public function testSelect_When_SpecificWhereParameterSupplied_Expect_ThreeRecords()
        {
            $schema = new Table(new Schema('\Test\Record', new class extends \PDO
            {
                public function __construct()
                {
                }

                public function prepare($query, $options = null)
                {
                    if (preg_match('/SELECT name AS _name FROM activiteit WHERE name = (?<namedParameter>:(\w+))/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }

                            public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
                            {

                            }

                            public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
                            {
                                if ($how === \PDO::FETCH_CLASS && $class_name === '\Test\Record\activiteit') {
                                    return [
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                    ];
                                }
                            }
                        };
                    }
                }
            }));

            $records = $schema->select('activiteit', ['name'], ['name' => 'foo']);

            $this->assertCount(3, $records);
        }

        public function testSelect_When_MultipleWhereParametersSupplied_Expect_OneRecord()
        {
            $schema = new Table(new Schema('\Test\Record', new class extends \PDO
            {
                public function __construct()
                {
                }

                public function prepare($query, $options = null)
                {
                    if (preg_match('/SELECT name AS _name FROM activiteit WHERE name = (?<namedParameter1>:(\w+)) AND id = (?<namedParameter2>:(\w+))/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }

                            public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
                            {

                            }

                            public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
                            {
                                if ($how === \PDO::FETCH_CLASS && $class_name === '\Test\Record\activiteit') {
                                    return [
                                        new class
                                        {
                                        }
                                    ];
                                }
                            }
                        };
                    }
                }
            }));

            $records = $schema->select('activiteit', ['name'], ['name' => 'foo', 'id' => '1']);

            $this->assertCount(1, $records);
        }


        public function testUpdate_When_NoWhereParametersSupplied_Expect_FiveUpdates()
        {
            $schema = new Table(new Schema('\Test\Record', new class extends \PDO
            {
                public function __construct()
                {
                }

                public function prepare($query, $options = null)
                {
                    if (preg_match('/UPDATE activiteit SET name = (?<namedSet1>:(\w+))/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }
                        };
                    } elseif (preg_match('/SELECT name AS _name FROM activiteit/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }

                            public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
                            {

                            }

                            public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
                            {
                                if ($how === \PDO::FETCH_CLASS && $class_name === '\Test\Record\activiteit') {
                                    return [
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        }
                                    ];
                                }
                            }
                        };
                    }
                }
            }));

            $this->assertCount(5, $schema->update('activiteit', ['name' => 'newName'], []));
        }

        public function testUpdate_When_SpecificWhereParameterSupplied_Expect_ThreeUpdates()
        {
            $schema = new Table(new Schema('\Test\Record', new class extends \PDO
            {
                public function __construct()
                {
                }

                public function prepare($query, $options = null)
                {
                    if (preg_match('/UPDATE activiteit SET name = (?<namedSet1>:(\w+)) WHERE name = (?<namedParameter1>:(\w+)) AND id = (?<namedParameter2>:(\w+))/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }
                        };
                    } elseif (preg_match('/SELECT name AS _name FROM activiteit WHERE name = (?<namedParameter1>:(\w+)) AND id = (?<namedParameter2>:(\w+))/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }

                            public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
                            {

                            }

                            public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
                            {
                                if ($how === \PDO::FETCH_CLASS && $class_name === '\Test\Record\activiteit') {
                                    return [
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        }
                                    ];
                                }
                            }
                        };
                    }
                }
            }));

            $this->assertCount(3, $schema->update('activiteit', ['name' => 'newName'], ['name' => 'oldName', 'id' => '1']));
        }

        public function testUpdate_When_MultipleWhereParameterSupplied_Expect_ThreeUpdates()
        {
            $schema = new Table(new Schema('\Test\Record', new class extends \PDO
            {
                public function __construct()
                {
                }

                public function prepare($query, $options = null)
                {
                    if (preg_match('/UPDATE activiteit SET name = (?<namedSet1>:(\w+)) WHERE name = (?<namedParameter1>:(\w+))/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }
                        };
                    } elseif (preg_match('/SELECT name AS _name FROM activiteit WHERE name = (?<namedParameter1>:(\w+))/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }

                            public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
                            {

                            }

                            public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
                            {
                                if ($how === \PDO::FETCH_CLASS && $class_name === '\Test\Record\activiteit') {
                                    return [
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        }
                                    ];
                                }
                            }
                        };
                    }
                }
            }));

            $this->assertCount(3, $schema->update('activiteit', ['name' => 'newName'], ['name' => 'oldName']));
        }


        public function testDelete_When_SingleParameter_Expect_One()
        {
            $schema = new Table(new Schema('\Test\Record', new class extends \PDO
            {
                public function __construct()
                {
                }

                public function prepare($query, $options = null)
                {
                    if (preg_match('/DELETE FROM activiteit WHERE name = (?<namedSet1>:(\w+))/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }
                        };
                    } elseif (preg_match('/SELECT name AS _name FROM activiteit WHERE name = (?<namedParameter1>:(\w+))/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }

                            public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
                            {

                            }

                            public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
                            {
                                if ($how === \PDO::FETCH_CLASS && $class_name === '\Test\Record\activiteit') {
                                    return [
                                        new class
                                        {
                                        }
                                    ];
                                }
                            }
                        };
                    }
                }
            }));

            $this->assertCount(1, $schema->delete('activiteit', ['name' => 'newName']));
        }


        public function testDelete_When_MultipleParameters_Expect_Five()
        {
            $schema = new Table(new Schema('\Test\Record', new class extends \PDO
            {
                public function __construct()
                {
                }

                public function prepare($query, $options = null)
                {
                    if (preg_match('/DELETE FROM activiteit WHERE id = (?<namedSet1>:(\w+)) AND name = (?<namedSet2>:(\w+))/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }
                        };
                    } elseif (preg_match('/SELECT id AS _id, name AS _name FROM activiteit WHERE id = (?<namedSet1>:(\w+)) AND name = (?<namedSet2>:(\w+))/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }

                            public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
                            {

                            }

                            public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
                            {
                                if ($how === \PDO::FETCH_CLASS && $class_name === '\Test\Record\activiteit') {
                                    return [
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        },
                                        new class
                                        {
                                        }
                                    ];
                                }
                            }
                        };
                    }
                }
            }));

            $this->assertCount(5, $schema->delete('activiteit', ['id' => '1', 'name' => 'newName']));
        }

        public function testInsert_When_NoWhereParametersSupplied_Expect_InsertedRecord()
        {

            $schema = new Table(new Schema('\Test\Record', new class extends \PDO
            {
                public function __construct()
                {
                }

                public function prepare($query, $options = null)
                {
                    if (preg_match('/INSERT INTO activiteit \(id, name\) VALUES \((?<namedSet1>:(\w+)), (?<namedSet2>:(\w+))\)/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }
                        };
                    } elseif (preg_match('/SELECT id AS _id, name AS _name FROM activiteit WHERE id = (?<namedSet1>:(\w+)) AND name = (?<namedSet2>:(\w+))/', $query, $match) === 1) {
                        return new class extends \PDOStatement
                        {
                            public function __construct()
                            {
                            }

                            public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
                            {
                                if ($how === \PDO::FETCH_CLASS && $class_name === '\Test\Record\activiteit') {
                                    return [
                                        new class
                                        {
                                            public $id = '1';
                                            public $name = 'newName';
                                        }
                                    ];
                                }
                            }
                        };
                    }
                }
            }));

            $this->assertEquals('newName', $schema->insert('activiteit', ['id' => '1', 'name' => 'newName'])[0]->name);
        }
    }
}