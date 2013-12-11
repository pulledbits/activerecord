<?php

/* 
 * application specific bootstrapper
 */

/**
 * @return \RestR
 */
return function() {
    $bootstrapper = require __DIR__ . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'bootstrap.php';
    
    $behavior = $bootstrapper();
        
    return $behavior;
};