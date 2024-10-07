<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 25 June 2024 */
/* This file is required for e.php and search.php */

if (!defined('ACCESS')) {
	die('Direct access not permitted to prelims.php');
}

$time = microtime();
$time = explode(' ', $time);
$starttime = $time[1] + $time[0];

// No PHP errors detected in testing so
// normally leave error reporting off
error_reporting(0);
ini_set('display_errors', 0);

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Page not editable so admin link (removed 25/06/24)

include(INC . 'error-reporting.php');
include(INC . 'lang.php');

// Next bit from top.php which is not loaded here
if ((APACHE === FALSE) || (!file_exists('./.htaccess'))) {
	$rewrite = FALSE;
} else {
	$rewrite = TRUE; // When not WINDOWS and .htaccess exists
}

?>