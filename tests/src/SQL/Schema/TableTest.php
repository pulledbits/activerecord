<?php
namespace ActiveRecord\SQL\Schema;

class TableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Table
     */
    private $object;

    protected function setUp()
    {
        $schema = new class implements \ActiveRecord\Schema {

            private function convertResultSet(array $results, \ActiveRecord\Schema\EntityType $entityType) {
                return array_map(function(array $values) use ($entityType) {
                    $schema = new class implements \ActiveRecord\Schema {

                        public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters, \ActiveRecord\Schema\EntityType $entityType): array
                        {
                            // TODO: Implement selectFrom() method.
                        }

                        public function updateWhere(string $tableIdentifier, array $setParameters, array $whereParameters): int
                        {
                            // TODO: Implement updateWhere() method.
                        }

                        public function insertValues(string $tableIdentifier, array $values): int
                        {
                            // TODO: Implement insertValues() method.
                        }

                        public function deleteFrom(string $tableIdentifier, array $whereParameters): int
                        {
                            // TODO: Implement deleteFrom() method.
                        }
                    };

                    return new \ActiveRecord\Entity($entityType, $schema, 'MyTable', $values, [], $values);
                }, $results);
            }

            public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters, \ActiveRecord\Schema\EntityType $entityType): array
            {
                $resultset = [];
                if ($tableIdentifier === 'activiteit') {
                    if ($columnIdentifiers === ['id', 'name']) {
                        if ($whereParameters === ['id' => '1', 'name' => 'newName']) {
                            $resultset = [
                                [
                                    'id' => '1',
                                    'name' => 'newName'
                                ],
                                [],
                                [],
                                [],
                                [],
                            ];
                        } else {
                            $resultset = [
                                [],
                                [],
                                [],
                                [],
                                [],
                            ];
                        }
                    } elseif ($columnIdentifiers === ['name']) {

                        if ($whereParameters === ['name' => 'foo']) {
                            $resultset = [
                                [],
                                [],
                                []
                            ];
                        } elseif ($whereParameters === ['name' => 'foo', 'id' => '1']) {
                            $resultset = [
                                []
                            ];
                        } elseif ($whereParameters === ['name' => 'oldName', 'id' => '1']) {
                            $resultset = [
                                []
                            ];
                        } elseif ($whereParameters === ['name' => 'oldName']) {
                            $resultset = [
                                [],
                                [],
                                []
                            ];
                        } elseif ($whereParameters === ['name' => 'newName']) {
                            $resultset = [
                                [],
                                [],
                                []
                            ];
                        } else {
                            $resultset = [
                                [],
                                [],
                                [],
                                [],
                                [],
                            ];
                        }
                    } elseif ($columnIdentifiers === ['nummer', 'collegejaar'] && $whereParameters === ['nummer' => '1', 'collegejaar' => '1415']) {
                        $resultset = [
                            [
                                'createdat' => date('Y-m-d'),
                                'collegejaar' => '1415',
                                'nummer' => '1',
                            ]
                        ];
                    } elseif ($columnIdentifiers === ['nummer'] && $whereParameters ===  ['createdat' => date('Y-m-d'), 'collegejaar' => '1415', 'nummer' => '1']) {
                        $resultset = [
                            [
                                'collegejaar' => '1415',
                                'nummer' => '2',
                            ]
                        ];
                    } elseif ($columnIdentifiers === ['collegejaar', 'nummer'] && $whereParameters === ['collegejaar' => '1415', 'nummer' => '2', 'createdat' => date('Y-m-d')]) {
                        $resultset = [
                            [
                                'collegejaar' => '1415',
                                'nummer' => '2',
                            ]
                        ];
                    } elseif ($columnIdentifiers === ['createdat', 'collegejaar', 'nummer'] && $whereParameters === ['createdat' => date('Y-m-d'), 'collegejaar' => '1415', 'nummer' => '1']) {
                        $resultset = [
                            [
                                'collegejaar' => '1415',
                                'nummer' => '2',
                            ]
                        ];
                    }
                } elseif ($tableIdentifier === 'thema') {
                    if ($columnIdentifiers === ['id', 'name']) {
                        $resultset = [
                            [
                                'id' => '1'
                            ],
                            [],
                            [],
                            [],
                            [],
                        ];
                    }
                }
                return $this->convertResultSet($resultset, $entityType);
            }

            public function updateWhere(string $tableIdentifier, array $setParameters, array $whereParameters): int
            {
                if ($tableIdentifier === 'activiteit') {
                    if ($setParameters === ['nummer' => '2'] && $whereParameters === ['createdat' => date('Y-m-d'), 'collegejaar' => '1415', 'nummer' => '1']) {
                        return 1;
                    } elseif ($setParameters === ['name' => 'newName'] && $whereParameters === []) {
                        return 5;
                    } elseif ($setParameters === ['name' => 'newName'] && $whereParameters === ['name' => 'oldName', 'id' => '1']) {
                        return 1;
                    } elseif ($setParameters === ['name' => 'newName'] && $whereParameters === ['name' => 'oldName']) {
                        return 3;
                    }
                }
                return 0;
            }

            public function insertValues(string $tableIdentifier, array $values): int
            {
                if ($tableIdentifier === 'activiteit'  && $values === ['id' => '1', 'name' => 'newName']) {
                    return 1;
                }
                return 0;
            }

            public function deleteFrom(string $tableIdentifier, array $whereParameters): int
            {
                if ($tableIdentifier === 'activiteit') {
                    if ($whereParameters ===  ['createdat' => date('Y-m-d'), 'collegejaar' => '1415', 'nummer' => '1']) {
                        return 1;
                    } elseif ($whereParameters ===  ['name' => 'newName']) {
                        return 3;
                    } elseif ($whereParameters === ['id' => '1', 'name' => 'newName']) {
                        return 5;
                    }
                }
                return 0;
            }
        };



        $this->object = new Table('activiteit', $schema);
    }

    public function testSelect_When_NoWhereParametersSupplied_Expect_FiveRecords()
    {
        $records = $this->object->select(['id', 'name'], []);
        $this->assertCount(5, $records);
    }

    public function testSelect_When_SpecificWhereParameterSupplied_Expect_ThreeRecords()
    {
        $records = $this->object->select(['name'], ['name' => 'foo']);
        $this->assertCount(3, $records);
    }

    public function testSelect_When_MultipleWhereParametersSupplied_Expect_OneRecord()
    {
        $records = $this->object->select(['name'], ['name' => 'foo', 'id' => '1']);
        $this->assertCount(1, $records);
    }

    public function testSelectFrom_When_NoWhereParametersSupplied_Expect_FiveRecords()
    {
        $records = $this->object->selectFrom('thema', ['id', 'name'], []);
        $this->assertCount(5, $records);
        $this->assertEquals('1', $records[0]->id);
    }

    public function testUpdate_When_NoWhereParametersSupplied_Expect_FiveUpdates()
    {
        $records = $this->object->update(['name' => 'newName'], []);
        $this->assertEquals(5, $records);
    }

    public function testUpdate_When_SpecificWhereParameterSupplied_Expect_ThreeUpdates()
    {
        $records = $this->object->update(['name' => 'newName'], ['name' => 'oldName', 'id' => '1']);
        $this->assertEquals(1, $records);
    }

    public function testUpdate_When_MultipleWhereParameterSupplied_Expect_ThreeUpdates()
    {
        $records = $this->object->update(['name' => 'newName'], ['name' => 'oldName']);
        $this->assertEquals(3, $records);
    }


    public function testDelete_When_SingleParameter_Expect_Three()
    {
        $records = $this->object->delete(['name' => 'newName']);
        $this->assertEquals(3, $records);
    }

    public function testDelete_When_MultipleParameters_Expect_Five()
    {
        $records = $this->object->delete(['id' => '1', 'name' => 'newName']);
        $this->assertEquals(5, $records);
    }

    public function testInsert_When_NoWhereParametersSupplied_Expect_InsertedRecord()
    {
        $this->assertEquals(1, $this->object->insert(['id' => '1', 'name' => 'newName']));
    }

    public function testExecuteRecordClassConfigurator_When_PathGiven_Expect_RecordClass()
    {
        file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'activiteit.php', '<?php
return function(\ActiveRecord\Schema\EntityType $entityType, array $values) {

                    $schema = new class implements \ActiveRecord\Schema {

                        public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters, \ActiveRecord\Schema\EntityType $entityType): array
                        {
                            // TODO: Implement selectFrom() method.
                        }

                        public function updateWhere(string $tableIdentifier, array $setParameters, array $whereParameters): int
                        {
                            // TODO: Implement updateWhere() method.
                        }

                        public function insertValues(string $tableIdentifier, array $values): int
                        {
                            // TODO: Implement insertValues() method.
                        }

                        public function deleteFrom(string $tableIdentifier, array $whereParameters): int
                        {
                            // TODO: Implement deleteFrom() method.
                        }
                    };

                    return new \ActiveRecord\Entity($entityType, $schema, \'MyTable\', $values, [], $values);
};');
        $record = $this->object->executeEntityConfigurator(sys_get_temp_dir(), ['status' => 'OK']);
        $this->assertEquals('OK', $record->status);
    }
}