<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 22 Dec 2022 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to error-reporting.php');
}

// error-reporting.php (this file) is the first file to be loaded by html.php
require(INC . 'settings.php');

if (defined('SHOW_ERRORS')) {
	if (SHOW_ERRORS) {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	} else {
		error_reporting(0);
	}
}

/**
 * Check the required files exist except html.php which loads this file
 * Language files except 'en' are checked on submit in /admin/language.php
 * Relative path to INC defined as required in /e.php and /inc/html.php
 */

$required = array('languages/de.php','languages/en.php','languages/es.php','languages/fr.php', '404.php', 'content.php', 'extra-body.php', 'extra-content.php', 'extra-head.php', 'footer.php', 'form.php', 'functions.php', 'lang.php', 'login-form.php', 'logout-form.php', 'menu.php', 'ppp.php', 'stylesheets.php', 'top.php');

if (defined('INC')) { // See html.php
	$missing = FALSE;

	foreach ($required as $file) {
		if (!file_exists(INC . $file)) { // If a file is missing
			echo "Error in /inc/error-reporting.php: the file '/inc/{$file}' does not exist. It must be installed.";
			$missing = TRUE;
			exit();
		}
	}

	require(INC . 'functions.php');

	// Check for new functions
	if (!function_exists('_print_nla')) {
		_print("Error in /inc/error-reporting.php: missing function '_print_nla'. Install the latest version of /inc/functions.php");
		exit();
	}

} else {
	echo "Error in /inc/error-reporting.php: the path 'INC' is not defined. Please install the latest version of superMicro CMS.";
}

?>