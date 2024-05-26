<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 09 Feb 2024 */
/* This file is required for e.php and s.php */

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

if (isset($_COOKIE['adminlink'])) {
	$admin = TRUE;
} else {
	$admin = FALSE;
}

if (file_exists(INC . 'error-reporting.php')) { // Does not load top.php
	require(INC . 'error-reporting.php');
} else {
	echo 'Error. Please install the file /inc/error-reporting.php';
	exit();
}

if (file_exists(INC . 'lang.php')) {
	require(INC . 'lang.php');
} else {
	echo 'Error. Please install the file /inc/lang.php';
	exit();
}

// Next bit from top.php which is not loaded here
if ((APACHE == FALSE) || (!file_exists('./.htaccess'))) {
	$rewrite = FALSE;
} else {
	$rewrite = TRUE; // When not WINDOWS and .htaccess exists
}

?>