<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 1-12-16
 * Time: 13:26
 */

namespace src;


use ActiveRecord\Table;


class TableTest extends \PHPUnit_Framework_TestCase
{
    public function testSelect_When_AllFields_ExpectAllRecords() {
        $pdo = new class extends \PDO {
            public function __construct(){}
            public function prepare($statement, $options = NULL) {
                if ($statement != "SELECT * FROM MyTable") {
                    return null;
                }
                return new class extends \PDOStatement {
                    public function __construct(){}
                    public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL) {
                        return [
                            new class() {
                                public function __get($property) {
                                    if ($property === 'id') {
                                        return 2;
                                    }
                                    return null;
                                }
                            }
                        ];
                    }
                };
            }
        };

        $table = new Table($pdo, "MyTable");
        $records = $table->select('*');

        $this->assertEquals($records[0]->id, 2);
    }

}
