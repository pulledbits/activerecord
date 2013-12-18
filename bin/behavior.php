#!/usr/bin/php
<?php

if (empty($_SERVER['argv'][1])) {
    exit('please provide directory');
}

$bootstrapper = require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$behavior = $bootstrapper();

$behavior->execute($_SERVER['argv'][1], 'App');
