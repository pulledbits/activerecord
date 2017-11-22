<?php


namespace pulledbits\ActiveRecord;


class Struct
{
    final public function __construct()
    {
        $propertyIndex = 0;
        foreach (get_object_vars($this) as $propertyIdentifier => $propertyValue) {
            if (func_num_args() === $propertyIndex) {
                break;
            }
            $this->$propertyIdentifier = func_get_arg($propertyIndex);
            $propertyIndex++;
        }
    }
}