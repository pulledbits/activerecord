<?php

namespace ActiveRecord;


interface Record {

    /**
     * @param $property
     * @return string
     */
    public function __get($property);

    public function primaryKey();

    /**
     * @return \ActiveRecord\WritableRecord[]
     */
    public function delete();

    /**
     * @param $property
     * @param $value
     * @return null
     */
    public function __set($property, $value);

}