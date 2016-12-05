<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:36
 */

namespace ActiveRecord;


class SchemaTest extends \PHPUnit_Framework_TestCase
{

    public function testDescribe_When_Default_Expect_ArrayWithClasses()
    {
        $schema = new SourceSchema(new class() extends \Doctrine\DBAL\Schema {
            public function __construct()
            {}
        });

        $schemaDescription = $schema->describe();

        $this->assertCount(1, $schemaDescription['classes']);
    }

}
