<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:36
 */

namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\SQL\Connection;
use function pulledbits\ActiveRecord\Test\createMockPDOMultiple;

class SchemaTest extends \PHPUnit_Framework_TestCase
{
    private $connection;
    private $schema;

    protected function setUp()
    {
        $this->connection = new Connection(createMockPDOMultiple([]));
        $this->schema = $this->connection->schema();
    }

    public function testConstructor_When_Default_Expect_ArrayWithRecordConfigurators()
    {
        $myTable = new TableDescription([], [], [
            'FkAnothertableRole' => [
                'table' => 'AnotherTable',
                'where' => [
                    'column_id' => 'extra_column_id'
                ],
            ]
        ]);
        $anotherTable = new TableDescription([], [], [
            'FkAnothertableRole' => [
                'table' => 'MyTable',
                'where' => [
                    'extra_column_id' => 'column_id'
                ],
            ]
        ]);

        $sourceSchema = new Schema([
            'MyTable' => $myTable,
            'AnotherTable' => $anotherTable
        ], []);

        $this->assertEquals(new Record($this->schema->makeRecordType('MyTable'), $myTable), $sourceSchema->describeTable($this->schema, 'MyTable'));
        $this->assertEquals(new Record($this->schema->makeRecordType('AnotherTable'), $anotherTable), $sourceSchema->describeTable($this->schema, 'AnotherTable'));
    }


    public function testDescribe_When_ViewAvailable_Expect_ArrayWithReadableClasses()
    {
        $schema = new Schema([], [
            'MyView' => 'CREATE VIEW `MyView` AS
  SELECT
    `schema`.`MyTable`.`name`   AS `name`,
    `schema`.`MyTable`.`birthdate` AS `birthdate`
  FROM `teach`.`thema`;'
        ]);

        $tableDescription = $schema->describeTable($this->schema, 'MyView');

        $this->assertEquals(new Record($this->schema->makeRecordType('MyView'), new TableDescription()), $tableDescription);
    }


    public function testDescribe_When_ViewWithUnderscoreNoExistingTableAvailable_Expect_ArrayWithReadableClasses()
    {
        $schema = new Schema( [], [
            'MyView_bla' => 'CREATE VIEW `MyView` AS
  SELECT
    `schema`.`MyTable`.`name`   AS `name`,
    `schema`.`MyTable`.`birthdate` AS `birthdate`
  FROM `teach`.`thema`;'
        ]);

        $tableDescription = $schema->describeTable($this->schema, 'MyView_bla');

        $this->assertEquals(new Record($this->schema->makeRecordType('MyView_bla'), new TableDescription()), $tableDescription);
    }

    public function testDescribe_When_ViewUsedWithExistingTableIdentifier_Expect_EntityTypeIdentifier()
    {

        $myTable = new TableDescription(['name', 'birthdate'], [], [
            'FkOthertableRole' => [
                'table' => 'OtherTable',
                'where' => [
                    'id' => 'role_id'
                ],
            ],
            'FkAnothertableRole' => [
                'table' => 'AntoherTable',
                'where' => [
                    'id' => 'role2_id'
                ],
            ],
            'FkAnothertableRole' => [
                'table' => 'AnotherTable',
                'where' => [
                    'column_id' => 'extra_column_id'
                ],
            ]
        ]);

        $schema = new Schema([
            'MyTable' => $myTable
            ],[
            'MyTable_today' => 'CREATE VIEW `MyTable_today` AS
  SELECT
    `schema`.`MyTable`.`name`   AS `name`,
    `schema`.`MyTable`.`birthdate` AS `birthdate`
  FROM `teach`.`MyTable`;'
        ]);

        $tableDescription = $schema->describeTable($this->schema, 'MyTable_today');

        $this->assertEquals(new WrappedEntity($schema->describeTable($this->schema, 'MyTable')), $tableDescription);
    }
}
