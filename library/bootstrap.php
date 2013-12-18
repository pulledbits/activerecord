<?php

/**
 * Behavior specific bootstrapper
 */

namespace Behavior;

/**
 * @return \Behavior
 */
return function() {
    if (!defined('NAMESPACE_SEPARATOR')) {
        define('NAMESPACE_SEPARATOR', '\\');
    }
    
    /**
     * Autoload-initializer
     * 
     * @param string $class The fully-qualified class name.
     * @return void
     */
    function autoload($base_dir, $prefix) {
        if (!is_dir($base_dir)) {
            return false;
        }
        
        return spl_autoload_register(function ($class) use ($prefix, $base_dir) {
            // does the class use the namespace prefix?
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                // no, move to the next registered autoloader
                return;
            }

            // get the relative class name
            $relative_class = substr($class, $len);

            // replace the namespace prefix with the base directory, replace namespace
            // separators with directory separators in the relative class name, append
            // with .php
            $file = $base_dir . DIRECTORY_SEPARATOR . str_replace(NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, $relative_class) . '.php';

            // if the file exists, require it
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
    
    if (autoload(__DIR__, __NAMESPACE__) === false) {
        throw new Exception\FailedAutoload('Bootstrap failed autoloading library');
    }

    require __DIR__ . DIRECTORY_SEPARATOR . 'Behavior.php';
    return new \Behavior(new Factory());
};