<?php

namespace pulledbits\ActiveRecord\Source;


use Psr\Http\Message\StreamInterface;

interface GeneratorGenerator
{
    public function generate(StreamInterface $stream) : void;
}