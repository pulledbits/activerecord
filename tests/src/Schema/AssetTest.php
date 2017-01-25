<?php
namespace ActiveRecord\Schema;

class AssetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Asset
     */
    private $object;

    protected function setUp()
    {
        file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'activiteit.php', '<?php
return function(\ActiveRecord\Schema\Asset $asset, array $values) {
    return new \ActiveRecord\Record($asset, $values, [], $values);
};');
        file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'thema.php', '<?php
return function(\ActiveRecord\Schema\Asset $asset, array $values) {
    return new \ActiveRecord\Record($asset, $values, [], $values);
};');

//        $schema = new \ActiveRecord\SQL\Schema(new \ActiveRecord\RecordFactory(sys_get_temp_dir()), \ActiveRecord\Test\createMockPDOMultiple([
//            '/^SELECT id, name FROM activiteit$/' => [
//                [],
//                [],
//                [],
//                [],
//                [],
//            ],
//            '/DELETE FROM activiteit WHERE name = :\w+/' => [],
//            '/^UPDATE activiteit SET name = :\w+ WHERE name = :\w+$/' => [],
//            '/^SELECT name FROM activiteit WHERE name = :\w+$/' => [
//                [],
//                [],
//                []
//            ],
//            '/^UPDATE activiteit SET name = :\w+ WHERE name = :\w+ AND id = :\w+$/' => [],
//            '/^SELECT name FROM activiteit WHERE name = :\w+ AND id = :\w+$/' => [
//                [],
//            ],
//            '/^SELECT id, name FROM thema$/' => [
//                [
//                    'id' => '1'
//                ],
//                [],
//                [],
//                [],
//                [],
//            ],
//            '/^UPDATE activiteit SET name = :\w+$/' => [],
//            '/^SELECT name FROM activiteit$/' => [
//                [],
//                [],
//                [],
//                [],
//                [],
//            ],
//            '/^INSERT INTO activiteit \(id, name\) VALUES \(:\w+, :\w+\)$/' => [],
//            '/^DELETE FROM activiteit WHERE id = :\w+ AND name = :\w+$/' => [],
//            '/^SELECT id, name FROM activiteit WHERE id = :\w+ AND name = :\w+$/' => [
//                [
//                    'id' => '1',
//                    'name' => 'newName'],
//                [],
//                [],
//                [],
//                [],
//            ],
//
//            // QUERIES FOR CRUD TEST
//            '/^SELECT collegejaar, nummer FROM activiteit WHERE collegejaar = :\w+ AND nummer = :\w+$/' => [],
//            '/INSERT INTO activiteit \(nummer, collegejaar\) VALUES \(:\w+, :\w+\)/' => [
//            ],
//            // SELECT AFTER INSERT
//            '/^SELECT nummer, collegejaar FROM activiteit WHERE nummer = :\w+ AND collegejaar = :\w+$/' => [
//                [
//                    'createdat' => date('Y-m-d'),
//                    'collegejaar' => '1415',
//                    'nummer' => '1',
//                ]
//            ],
//            '/^UPDATE activiteit SET nummer = :\w+ WHERE createdat = :\w+ AND collegejaar = :\w+ AND nummer = :\w+/' => 1,
//            // SELECT AFTER UPDATE
//            '/^SELECT nummer FROM activiteit WHERE createdat = :\w+ AND collegejaar = :\w+ AND nummer = :\w+$/' => [
//                [
//                    'collegejaar' => '1415',
//                    'nummer' => '2',
//                ]
//            ],
//            // CONFIRM UPDATE SELECT
//            '/^SELECT collegejaar, nummer FROM activiteit WHERE collegejaar = :\w+ AND nummer = :\w+ AND createdat = :\w+/' => [
//                [
//                    'collegejaar' => '1415',
//                    'nummer' => '2',
//                ]
//            ],
//            '/^DELETE FROM activiteit WHERE createdat = :\w+ AND collegejaar = :\w+ AND nummer = :\w+$/' => [],
//            // SELECT AFTER DELETE
//            '/^SELECT createdat, collegejaar, nummer FROM activiteit WHERE createdat = :\w+ AND collegejaar = :\w+ AND nummer = :\w+$/' => [
//                [
//                    'collegejaar' => '1415',
//                    'nummer' => '2',
//                ]
//            ],
//        ]));


        $schema = new class implements \ActiveRecord\Schema {

            private function convertResultSet(array $results, \Closure $recordConverter) {
                return array_map(function(array $values) use ($recordConverter) {
                    return $recordConverter(function(\ActiveRecord\Schema\Asset $asset) use ($values) {
                        return $asset->executeRecordClassConfigurator(sys_get_temp_dir(), $values);
                    });
                }, $results);
            }

            public function selectFrom(string $tableIdentifier, array $columnIdentifiers, array $whereParameters, \Closure $recordConverter): array
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
                return $this->convertResultSet($resultset, $recordConverter);
            }

            public function updateWhere(string $tableIdentifier, array $setParameters, array $whereParameters): int
            {
                if ($tableIdentifier === 'activiteit') {
                    if ($setParameters === ['nummer' => '1'] && $whereParameters ===  ['createdat' => date('Y-m-d'), 'collegejaar' => '1415', 'nummer' => '1']) {
                        return 1;
                    }
                }
                return 0;
            }

            public function insertValues(string $tableIdentifier, array $values): int
            {
                return 0;
            }

            public function deleteFrom(string $tableIdentifier, array $whereParameters): int
            {
                return 0;
            }
        };

        $this->object = new Asset('activiteit', $schema);
    }

    public function testCRUD_When_DefaultState_Expect_RecordCreatedSelectedUpdatedAndDeleted() {
        $this->assertCount(0, $this->object->select(['collegejaar', 'nummer'], ['collegejaar' => '1415', 'nummer' => '2']), 'no previous record exists');
        $record = $this->object->insert(['nummer' => '1', 'collegejaar' => '1415'], [])[0];
        $this->assertEquals('1', $record->nummer, 'record is properly initialized');
        $record->nummer = '2';
        $this->assertEquals($record->nummer, $this->object->select(['collegejaar', 'nummer'], ['collegejaar' => '1415', 'nummer' => '2', 'createdat' => date('Y-m-d')])[0]->nummer, 'record is properly updated');
        $this->assertCount(1, $record->delete(), 'delete confirms removal');
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
        $this->assertCount(5, $records);
    }

    public function testUpdate_When_SpecificWhereParameterSupplied_Expect_ThreeUpdates()
    {
        $records = $this->object->update(['name' => 'newName'], ['name' => 'oldName', 'id' => '1']);
        $this->assertCount(1, $records);
    }

    public function testUpdate_When_MultipleWhereParameterSupplied_Expect_ThreeUpdates()
    {
        $records = $this->object->update(['name' => 'newName'], ['name' => 'oldName']);
        $this->assertCount(3, $records);
    }


    public function testDelete_When_SingleParameter_Expect_Three()
    {
        $records = $this->object->delete(['name' => 'newName']);
        $this->assertCount(3, $records);
    }

    public function testDelete_When_MultipleParameters_Expect_Five()
    {
        $records = $this->object->delete(['id' => '1', 'name' => 'newName']);
        $this->assertCount(5, $records);
    }

    public function testInsert_When_NoWhereParametersSupplied_Expect_InsertedRecord()
    {
        $records = $this->object->insert(['id' => '1', 'name' => 'newName']);
        $this->assertEquals('newName', $records[0]->name);
    }

    public function testExecuteRecordClassConfigurator_When_PathGiven_Expect_RecordClass()
    {
        $record = $this->object->executeRecordClassConfigurator(sys_get_temp_dir(), ['status' => 'OK']);
        $this->assertEquals('OK', $record->status);
    }
}