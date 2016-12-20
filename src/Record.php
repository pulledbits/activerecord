<?php

namespace ActiveRecord;


interface Record {

    static function wherePrimaryKey(array $values);

    /**
     */
    public function delete();

    /**
     */
    public function fetchAll();
}