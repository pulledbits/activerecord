<?php

function array_slice_key(array $array, array $keys) {
    $sliced = [];
    foreach ($array as $key => $value) {
        if (in_array($key, $keys, true)) {
            $sliced[$key] = $value;
        }
    }
    return $sliced;
}


return function() {
	require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
};