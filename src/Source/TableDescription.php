<?php
namespace pulledbits\ActiveRecord\Source;


use pulledbits\ActiveRecord\Struct;

class TableDescription extends Struct
{
    public $identifier = [];
    public $requiredAttributeIdentifiers = [];
    public $references = [];
}