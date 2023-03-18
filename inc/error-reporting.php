<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 17 March 2023 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to error-reporting.php');
}

// error-reporting.php (this file) is the first file to be loaded by html.php
require(INC . 'settings.php');

if (!defined('SHOW_ERRORS')) {
	error_reporting(0);
}

if (defined('SHOW_ERRORS') && SHOW_ERRORS === TRUE) {
	// Display errors
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	// Log errors (optional)
	// ini_set('log_errors', 1);
	// ini_set('error_log', '/path/to/error/log/file');
}

if (defined('INC')) { // See html.php
	require(INC . 'functions.php');

	// Check for new functions
	// if (!function_exists('_print_nla')) {
	// 	_print("Error in /inc/error-reporting.php: missing function '_print_nla'. Install the latest version of /inc/functions.php");
	// 	exit();
	// }

} else {
	echo "Error in /inc/error-reporting.php: the path 'INC' is not defined.";
	exit();
}

?>