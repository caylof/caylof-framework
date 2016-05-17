<?php

require __DIR__.'/../../autoload.php';


function print_var($var) {
    print '<pre>';
    var_dump($var);
    print '</pre>';
}

function debug($var) {
    print_var($var);
    exit();
}


if(!function_exists('array_column')) {

    function array_column($input, $column_key, $index_key = null) {

        $result = [];

        foreach($input as $arr) {
            if(!is_array($arr)) continue;

            $value = is_null($column_key) ? $arr : $arr[$column_key];

            if(!is_null($index_key)) {
                $key = $arr[$index_key];
                $result[$key] = $value;
            } else {
                $result[] = $value;
            }

        }

        return $result;
    }
}
