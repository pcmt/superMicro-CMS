<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 04 Dec 2020 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to error-reporting.php.');
}

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

$required = array('languages/de.php','languages/en.php','languages/es.php','languages/fr.php', '404.php', 'content.php', 'extra-body.php', 'extra-content.php', 'extra-head.php', 'footer.php', 'functions.php', 'lang.php', 'login-form.php', 'logout-form.php', 'menu.php', 'ppp.php', 'stylesheets.php', 'top.php');

if (defined('INC')) { // See html.php
	$missing = FALSE;

	foreach ($required as $file) {
		if (!file_exists(INC . $file)) { // If a file is missing
			echo "Error. The file '/inc/{$file}' does not exist. It must be installed.";
			$missing = TRUE;
			exit();
		}
	}

	// error-reporting.php is the first file to be loaded by html.php
	require(INC . 'settings.php');
	require(INC . 'functions.php');

} else {
	echo "Error. The path 'INC' is not defined. Please install the latest version of superMicro CMS.";
}

?>