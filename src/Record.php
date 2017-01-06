<?php

namespace ActiveRecord;


interface Record {

    /**
     * @return \ActiveRecord\Record[]
     */
    public function delete();

    /**
     * @param $property
     * @param $value
     * @return null
     */
    public function __set($property, $value);

}