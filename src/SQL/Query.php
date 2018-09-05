<?php


namespace pulledbits\ActiveRecord\SQL;


interface Query
{
    public function execute(Connection $connection) : \pulledbits\ActiveRecord\Result;
}