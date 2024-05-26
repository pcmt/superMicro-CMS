<?php
/**
 * Qwwwik
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 23 March 2024 */

// Declare variables
$protected = $nocopy = $adminlink = $siteID = $admin = $rewrite = $the_page = $to_omit = $skip = $found = $history = $history_array = $update = $a = $b = $c = $cookie_folder = "";

if (!defined('ACCESS')) {
	die('Direct access not permitted to top.php');
}

// See also footer.php
$time = microtime();
$time = explode(' ', $time);
$starttime = $time[1] + $time[0];

if ($password) { // From html.php
	$titletag = 'Password Protected';
	include(INC . 'ppp.php');
	$protected = TRUE;
}

if (defined('SITE_ID')) {
	$adminlink = 'adminlink_' . SITE_ID;
	$siteID = SITE_ID;
} else {
	$adminlink = FALSE;
}

// For one-hour admin link in menu.php - be careful: reveals admin folder
// Accessing the cookie only shows the link (can theoretically be logged out)
// Logout MUST delete the cookie
if (isset($_COOKIE[$adminlink]) && (($_COOKIE[$adminlink] == $siteID) || (stripos(LOCATION, 'localhost') != FALSE))) {
	$admin = TRUE;
} else {
	$admin = FALSE;
}

/**
 * System URLs only
 * URLs with no .php file suffix depend on .htaccess and mod_rewrite module
 *
 * apache_get_modules is used to detect mod_rewrite but this is not reliable
 * because it works only when PHP is installed as an Apache module and doesn't
 * work with fastCGI, FPM or nginx, so can't use apache_get_modules (not used)
 *
 * menu.php, e.php, s.php need to know whether to add the suffix so this file's
 * $rewrite variable should tell them (PHP mail function won't work on WINDOWS)
 *
 * settings.php has APACHE constant TRUE or FALSE to enable .php URL suffix,
 * otherwise assume .htaccess / mod_rewrite are functional and remove suffix
 */

// Assemble the canonical URL
global $rewrite; // Make available throughout

if (APACHE) { // Is Apache
	$rewrite = TRUE; // No .php file extensions
	$the_page = $pageID; // Both the same
} else { // Not Apache
	$rewrite = FALSE; // Has .php file extensions
	$the_page = $pageID . '.php'; // Add extension
}

if ($pageID == 'index') { // Home page
	$the_page = '';
}

// See functions.php and content.php re PHP_EXT constant

$canonical = "";
$canonical = LOCATION . $the_page;

/* ================================================== */
/* Pages last viewed */

if (defined('SHOW_HISTORY') && (SHOW_HISTORY === TRUE)) {

	// Omit pages
	$to_omit = array('index', 'preview');
	$skip = FALSE;
	foreach ($to_omit as $found) {
		if ($found == $pageID) {
			$skip = TRUE;
		}
	}

	$smcms_history = 'x' . SITE_ID;

	// Get the history
	if (isset($_COOKIE[$smcms_history])) {

		$history = $_COOKIE[$smcms_history];
		// Whitespace changed to underscore (no whitespace in cookie values)
		// See corresponding 'explode' in history.php
		$historyArray = explode("_", $history); // Make array

		if (!$skip) {
			// If the current page is already in the cookie, do nothing
			// otherwise add it to the cookie for next page view.
			// If it is not in the cookie and the page is refreshed,
			// it will be added. The history will then show the current page.
			if (strpos($history, $pageID) === FALSE) {

				// Number of values
				$num = count($historyArray);

				// The values
				if (isset($historyArray[0])) {
					$a = $historyArray[0];
				} else {
					$a = "";
				}
				if (isset($historyArray[1])) {
					$b = $historyArray[1];
				} else {
					$b = "";
				}
				if (isset($historyArray[2])) {
					$c = $historyArray[2];
				} else {
					$c = "";
				}

				if ($pageID != 'index') { // Exclude home page
					// $update is the cookie value (no whitespace)
					$update = $pageID . '_' . $a . '_' . $b; // Add this page
				} else { // Is home page
					$update = $a . '_' . $b . '_' . $c; // Home page (do nothing)
				}

				// Update cookie with this page ID, $a and $b being moved along and $c dropped
				// $update may have a trailing underscore (rtrimmed in history.php)
				setcookie($smcms_history, trim($update), time() + 31556926, $cookie_folder); // One year
			}
		}

	} else {

		if (!$skip) {
			setcookie($smcms_history, $pageID, time() + 31556926, $cookie_folder); // One year
		}

	}
}


/* End pages last viewed */
/* ================================================== */

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php _print($titletag); ?></title>
<link rel="canonical" href="<?php _print($canonical); ?>">
<link rel="shortcut icon" href="favicon.ico">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<?php

if (file_exists(INC . 'stylesheets.php')) {
	include(INC . 'stylesheets.php');
	_print("\n");
}

if (file_exists(INC . 'extra-head.php')) {
	include(INC . 'extra-head.php');
}

if ($protected) {
	_print_nla('<meta name="robots" content="noindex,nofollow">');
}

if ($protected || $nocopy) { // See html.php
	_print('
<style>

@media print {
  #wrap {
    display: none;
  }
}

#wrap {
  -webkit-touch-callout: none; /* iOS Safari */
    -webkit-user-select: none; /* Safari */
     -khtml-user-select: none; /* Konqueror HTML */
       -moz-user-select: none; /* Old versions of Firefox */
        -ms-user-select: none; /* Internet Explorer/Edge */
            user-select: none; /* Non-prefixed version, currently
                                  supported by Chrome, Edge, Opera and Firefox */
}

</style>
');
} else {
	_print("\n");
}

?>

</head>
<?php
if (!$protected && !$nocopy) {
	_print("<body>\n");
} else {
	_print('<body oncontextmenu="return false" onselectstart="return false" ondragstart="return false">');
	_print("\n");
}
?>