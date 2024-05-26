<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 09 Feb 2024 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to error-reporting.php');
}

if (!defined('SHOW_ERRORS')) {
	error_reporting(0);
}

if (defined('INC')) { // See html.php
	require(INC . 'settings.php');
	require(INC . 'functions.php');
} else {
	echo "Error in /inc/error-reporting.php: the path 'INC' is not defined.";
	exit();
}

if (defined('SHOW_ERRORS') && SHOW_ERRORS === TRUE) { // Needs settings.php
	// Display errors
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

?>