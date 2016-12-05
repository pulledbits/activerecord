<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 5-12-16
 * Time: 12:36
 */

namespace src;


use ActiveRecord\Schema;


class SchemaTest extends \PHPUnit_Framework_TestCase
{

    public function testQuery_When_QueryAndParametersSupplied_Expect_ResultSet()
    {
        $schema = new Schema();
        $resultset = $schema->query('SELECT * FROM activiteit WHERE id = :id', [
            'id' => 1
        ]);

        $this->assertCount(1, $resultset);
    }

}
