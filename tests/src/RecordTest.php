<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 25-1-17
 * Time: 15:50
 */

namespace ActiveRecord;


class RecordTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $asset = new \ActiveRecord\Schema\Asset('activeit', new Schema(new RecordFactory(sys_get_temp_dir()), \ActiveRecord\Test\createMockPDOMultiple([])));
        $primaryKey = [];
        $references = [];
        $values = [
            'number' => '1'
        ];
        $this->object = new Record($asset, $primaryKey, $references, $values);
    }

    public function test__get_When_ExistingProperty_Expect_Value()
    {
        $value = $this->object->number;
        $this->assertEquals('1', $value);
    }


}
