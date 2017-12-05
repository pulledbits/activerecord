<?php

namespace pulledbits\ActiveRecord;

interface Result extends \Countable
{
    public function fetchAll(): array;
}