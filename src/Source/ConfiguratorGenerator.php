<?php

namespace pulledbits\ActiveRecord\Source;


use Psr\Http\Message\StreamInterface;

interface ConfiguratorGenerator
{
    public function generate(StreamInterface $stream) : void;
}