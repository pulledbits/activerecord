<?php

namespace pulledbits\ActiveRecord\Source;


use Psr\Http\Message\StreamInterface;

interface ConfiguratorGenerator
{
    public function generateConfigurator(StreamInterface $stream) : void;
}