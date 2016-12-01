<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 1-12-16
 * Time: 13:32
 */

namespace ActiveRecord;


class Table
{
    public function select(string $fields) {
        return [
            (object)['id' => 1]
        ];
    }
}