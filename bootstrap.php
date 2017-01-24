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

function var_export_short(array $array, $return = false, $indentchar = "\t", $level = 1) {
    $indent = str_repeat($indentchar, $level);

    $lines = [];
    foreach ($array as $key => $value) {
        $line = $indent;
        if (is_int($key)) {
            $line .= $key;
        } else {
            $line .= '\'' . $key . '\'';
        }
        $line .= ' => ';
        switch (gettype($value)) {
            case 'string':
                $line .= '\'' . $value . '\'';
                break;
            case 'int':
                $line .= $value;
                break;
            case 'array':
                $line .= var_export_short($value, $return, $indentchar, $level + 1);
                break;
        }
        $lines[] = $line;
    }

    $export = '[' . PHP_EOL . join(', ' . PHP_EOL, $lines) . PHP_EOL . str_repeat($indentchar, $level - 1) . ']';
    if ($return) {
        return $export;
    } else {
        print $export;
    }
}

return function() {
	require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
};