<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 15-12-16
 * Time: 15:16
 */

namespace ActiveRecord;


class SchemaTest extends \PHPUnit_Framework_TestCase
{
    public function testSelect_When_NoWhereParametersSupplied_Expect_FiveRecords()
    {
        $schema = new Schema('\Database', new class extends \PDO {
            public function __construct() {}

            public function prepare($query, $options = null) {
                if ($query === 'SELECT * FROM activiteit') {
                    return new class extends \PDOStatement {
                        public function __construct() {}
                        public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL) {
                            if ($how === \PDO::FETCH_CLASS && $class_name === '\Database\Record\activiteit') {
                                return [
                                    new class {},
                                    new class {},
                                    new class {},
                                    new class {},
                                    new class {},
                                ];
                            }
                        }
                    };
                }
            }
        });

        $records = $schema->select('activiteit', []);

        $this->assertCount(5, $records);
    }

}
