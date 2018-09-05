<?php


namespace pulledbits\ActiveRecord\SQL\MySQL;


interface Query
{
    public function execute(\pulledbits\ActiveRecord\SQL\Connection $connection) : Query\Result;
}