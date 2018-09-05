<?php


namespace pulledbits\ActiveRecord\SQL;


interface Query
{
    public function execute() : \pulledbits\ActiveRecord\Result;
}