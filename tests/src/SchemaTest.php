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
    public function testTransformColumnToProperty_When_ColumnIdentifierSupplied_Expect_ColumnIdPrefixedWithUnderscore()
    {

        $schema = new Schema('\Test\Record', new class extends \PDO
        {
            public function __construct()
            {
            }
        });

        $this->assertEquals("_id", $schema->transformColumnToProperty('id'));
    }

    public function testTransformTableIdentifierToRecordClassIdentifier_When_TableIdentifierSupplied_Expect_TableIdPrefixedWithTargetNamespace()
    {

        $schema = new Schema('\Test\Record', new class extends \PDO
        {
            public function __construct()
            {
            }
        });

        $this->assertEquals('\Test\Record\activiteit', $schema->transformTableIdentifierToRecordClassIdentifier('activiteit'));
    }
}