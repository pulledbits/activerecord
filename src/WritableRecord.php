<?php

namespace ActiveRecord;


interface WritableRecord {

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