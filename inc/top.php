<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 14 May 2023 */

// Declare variables
$protected = $the_page = $adminlink = $admin = "";

if (!defined('ACCESS')) {
	die('Direct access not permitted to top.php');
}

// See also footer.php
$time = microtime();
$time = explode(' ', $time);
$starttime = $time[1] + $time[0];

if ($password) { // From html.php

	$titletag = 'Password Protected';

	if (file_exists(INC . 'ppp.php')) {
		include(INC . 'ppp.php');
		$protected = TRUE;
	} else {
		_print("Error in /inc/top.php: '/inc/ppp.php' could not be found.");
		exit(); // For security (file has to exist)
	}

}

if (defined('SITE_ID')) {
	$adminlink = 'adminlink_' . SITE_ID;
} else {
	$adminlink = 'x';
}

// For one-hour admin link in menu.php - be careful: reveals admin folder
// Accessing the cookie only shows the link (can theoretically be logged out)
// Logout MUST delete the cookie
if (isset($_COOKIE[$adminlink]) && (($_COOKIE[$adminlink] == 'loggedin_sec') || (stripos(LOCATION, 'localhost') !== FALSE))) {
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

if (APACHE == FALSE) { // Not Apache
	$rewrite = FALSE; // Has .php file extensions
	$the_page = $pageID . '.php'; // Add extension
} else {
	$rewrite = TRUE; // No .php file extensions
	$the_page = $pageID; // Both the same
}

if ($pageID == 'index') { // Home page
	$the_page = '';
}

// See functions.php and content.php re PHP_EXT constant

$canonical = "";
$canonical = LOCATION . $the_page;

?>
<!DOCTYPE html>
<html<?php

if (defined('LANG_ATTR')) {
	_print(' lang="' . LANG_ATTR . '"');
}

?>>
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

if ($protected || $nocopy) {
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
