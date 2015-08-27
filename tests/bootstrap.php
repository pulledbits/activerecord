<?php

/* 
 * test specific bootstrapper
 */
return function() {
	$applicationBootstrap = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';
	$applicationBootstrap();
};