<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:06
 */

namespace ActiveRecord\SQL;

class SchemaTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Schema
     */
    private $object;

    protected function setUp()
    {
        $recordConfiguration = new \ActiveRecord\RecordFactory(sys_get_temp_dir());
        $this->object = new Schema($recordConfiguration, \ActiveRecord\Test\createMockPDOMultiple([
            '/SELECT \* FROM activiteit WHERE id = :\w+$/' => [
                [
                    'werkvorm' => 'BlaBla'
                ],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                [],
                []
            ],
            '/SELECT id AS _id, werkvorm AS _werkvorm FROM activiteit WHERE id = :param1$/' => [
                [],
                [],
                [],
                [],
                []
            ],
            '/^SELECT id AS _id, werkvorm AS _werkvorm FROM activiteit WHERE werkvorm = :\w+$/' => [
                []
            ],
            '/^UPDATE activiteit SET werkvorm = :\w+ WHERE id = :\w+$/' => 1,
            '/^INSERT INTO activiteit \(werkvorm, id\) VALUES \(:\w+, :\w+\)$/' => 1,
            '/SELECT id, werkvorm FROM activiteit WHERE id = :\w+$/' => [
                [
                    'werkvorm' => 'Bla'
                ],
                [],
                [],
                []
            ],
            '/SELECT id, werkvorm FROM activiteit WHERE id = :\w+ AND foo = :\w+$/' => [],
            '/^DELETE FROM activiteit WHERE id = :\w+$/' => 1,
            '/^DELETE FROM activiteit WHERE sid = :\w+$/' => false,
        ]));
    }

    public function testUpdateWhere_When_DefaultState_Expect_SQLUpdateQueryWithWhereStatementAndParameters() {
        $this->assertEquals(1, $this->object->update('activiteit', ['werkvorm' => 'My Name'], ['id' => '3']));
    }

    public function testInsertValue_When_DefaultState_Expect_SQLInsertQueryWithPreparedValues() {
        $this->assertEquals(1, $this->object->create('activiteit', ['werkvorm' => 'My Name', 'id' => '3']));
    }

    public function testInitializeRecord_When_DefaultState_Expect_UncommittedRecord() {
        $record = $this->object->initializeRecord('activiteit', ['name' => 'blabla']);
        $this->assertEquals('blabla', $record->name);
    }

    public function testSelectFrom_When_NoColumnIdentifiers_Expect_SQLSelectAsteriskQueryAndCallbackUsedForFetchAll() {
        $records = $this->object->read('activiteit', [], ['id' => '1']);

        $this->assertCount(10, $records);
        $this->assertEquals('BlaBla', $records[0]->werkvorm);
    }

    public function testSelectFrom_When_DefaultState_Expect_SQLSelectQueryAndCallbackUsedForFetchAll() {
        $records = $this->object->read('activiteit', ['id', 'werkvorm'], ['id' => '1']);

        $this->assertCount(4, $records);
        $this->assertEquals('Bla', $records[0]->werkvorm);
    }

    public function testReadFirst_When_DefaultState_Expect_SQLSelectQueryAndCallbackUsedForFetchAll() {
        $record = $this->object->readFirst('activiteit', ['id', 'werkvorm'], ['id' => '1']);
        $this->assertEquals('Bla', $record->werkvorm);
    }

    public function testReadFirst_When_NoMatchingConditions_Expect_DummyEntityWithConditionsAsValue() {
        $record = $this->object->readFirst('activiteit', ['id', 'werkvorm'], ['id' => '2323', 'foo' => 'bar']);
        $this->assertNull($record->werkvorm);
        $this->assertEquals('2323', $record->id);
        $this->assertEquals('bar', $record->foo);
    }

    public function testDeleteFrom_When_DefaultState_Expect_SQLDeleteQuery() {
        $this->assertEquals(1, $this->object->delete('activiteit', ['id' => '3']));
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     * @expectedExceptionMessageRegExp /^Failed executing query/
     */
    public function testDeleteFrom_When_Erroneous_Expect_Warning() {
        $this->assertEquals(0, $this->object->delete('activiteit', ['sid' => '3']));
    }

}