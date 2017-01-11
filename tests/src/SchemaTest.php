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
        $this->object = new Schema('\Test\Record', \ActiveRecord\Test\createMockPDO('/SELECT id AS _id, name AS _name FROM activiteit WHERE id = :param1/', [
                [],
                [],
                [],
                [],
                []
            ]
        ));
    }

    public function testTransformTableIdentifierToRecordClassIdentifier_When_TableIdentifierSupplied_Expect_TableIdPrefixedWithTargetNamespace()
    {
        $this->assertEquals('\Test\Record\activiteit', $this->object->transformTableIdentifierToRecordClassIdentifier('activiteit'));
    }

    public function testExecute_When_WhenProperQueryWithNamedParametersSupplied_Expect_PDOStatementWithFiveRecords()
    {
        $statement = $this->object->execute('SELECT id AS _id, name AS _name FROM activiteit WHERE id = :param1', [':param1' => '1']);
        $this->assertCount(5, $statement->fetchAll(\PDO::FETCH_ASSOC));
    }
}