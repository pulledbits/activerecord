<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:06
 */

namespace ActiveRecord;


class SchemaTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Schema
     */
    private $object;

    protected function setUp()
    {
        $this->object = new Schema('\Test\Record', \ActiveRecord\Test\createMockPDO('', []));
    }

    public function testTransformTableIdentifierToRecordClassIdentifier_When_TableIdentifierSupplied_Expect_TableIdPrefixedWithTargetNamespace()
    {
        $this->assertEquals('\Test\Record\activiteit', $this->object->transformTableIdentifierToRecordClassIdentifier('activiteit'));
    }

    public function testPrepareParameters_When_ColumnIdentifiersSupplied_Expect_ColumnIdPrefixedWithUnderscore()
    {
        $namedParameter = ':' . sha1('where_id');
        $this->assertEquals([["id" => "id = " . $namedParameter], [$namedParameter => '1']], $this->object->prepareParameters('where', ['id' => '1']));
    }

    public function testMakeWhereCondition_When_NoColumnIdentifiersAndValuesSupplied_Expect_NoProperSQLWhereConditionAndNamedParameters()
    {
        $namedParameters = [];
        $this->assertEquals("", $this->object->makeWhereCondition([], $namedParameters));
        $this->assertCount(0, $namedParameters);
    }

    public function testMakeWhereCondition_When_ColumnIdentifiersAndValuesSupplied_Expect_ProperSQLWhereConditionAndNamedParameters()
    {
        $namedParameterId = ':' . sha1('where_id');
        $namedParameterName = ':' . sha1('where_name');

        $namedParameters = [];
        $this->assertEquals(" WHERE id = $namedParameterId AND name = $namedParameterName", $this->object->makeWhereCondition(['id' => '1', 'name' => 'MYName'], $namedParameters));
        $this->assertEquals('1', $namedParameters[$namedParameterId]);
        $this->assertEquals('MYName', $namedParameters[$namedParameterName]);
    }

    public function testExecute_When_WhenProperQueryWithNamedParametersSupplied_Expect_PDOStatementWithFiveRecords()
    {
        $schema = new Schema('\Test\Record', \ActiveRecord\Test\createMockPDO('/SELECT id AS _id, name AS _name FROM activiteit WHERE id = :param1/', [
                [],
                [],
                [],
                [],
                []
            ]
        ));

        $statement = $schema->execute('SELECT id AS _id, name AS _name FROM activiteit WHERE id = :param1', [':param1' => '1']);

        $this->assertCount(5, $statement->fetchAll(\PDO::FETCH_ASSOC));
    }
}