<?php

/*
 *  © Chris How, Primesolid 2015
 *  All rights reserved.
 */

// All functionality in /lib, bootstrapped here

require_once('lib/mcg-theme.php');

$mcg_theme = new MCG_Theme();


// Utils

if (!function_exists('get_var')) {
    function get_var($key, $where = NULL) {
	if ($where === NULL) {
	    $where = $_REQUEST;
	}

	if (isset($where[$key])) {
	    return $where[$key];
	} else {
	    return NULL;
	}
    }
}

if (!function_exists('pp')) {
    function pp($what) {
	echo "<pre>" . PHP_EOL;
	var_dump($what);
	echo "</pre>" . PHP_EOL;
    }
}

