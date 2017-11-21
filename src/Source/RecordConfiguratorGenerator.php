<?php

namespace pulledbits\ActiveRecord\Source;


use Psr\Http\Message\StreamInterface;

interface RecordConfiguratorGenerator
{
    public function generateConfigurator(StreamInterface $stream) : void;
}