<?php

namespace ActiveRecord;


interface ReadableRecord {

    /**
     * @param $property
     * @return string
     */
    public function __get($property);

    public function primaryKey();

}