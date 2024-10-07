<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 20 August 2024 */

// Declare variables
$protected = $adminlink = $siteID = $admin = $rewrite = $the_page = $description = "";

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

if (file_exists(INC . 'show-history.php')) {
	include(INC . 'show-history.php');
}

/* ================================================== */
/* Process the data */

if (file_exists(INC . 'process-data.php')) {
	include(INC . 'process-data.php');
}

?>

<!DOCTYPE html>
<html<?php

if (defined('LANG_ATTR')) {
	_print(' lang="' . LANG_ATTR . '"');
} else {
	_print(' lang="en"');
}

?>>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if (FONT_TYPE === 'hosted') { ?>
<link rel="preload" as="font" href="fonts/open-sans-v40-latin-regular.woff2" type="font/woff2" crossorigin>
<link rel="preload" as="font" href="fonts/ubuntu-v20-latin-500.woff2" type="font/woff2" crossorigin>
<link rel="preload" as="font" href="fonts/open-sans-v40-latin-600.woff2" type="font/woff2" crossorigin>
<?php } ?>
<title><?php _print($titletag); ?></title>
<?php
if ($structured_data) {
	_print_nlb($structured_data);
}
if ($description && strlen($description) > 12) {
	_print_nlb('<meta name="description" content="' . $description . '">');
}
?>
<link rel="canonical" href="<?php _print($canonical); ?>">
<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
<link rel="manifest" href="site.webmanifest">
<?php

include(INC . 'stylesheets.php');
include(INC . 'extra-head.php');

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
	_print_nlb("<body>");
} else {
	_print_nlb('<body oncontextmenu="return false" onselectstart="return false" ondragstart="return false">');
}

if ($share) {
	_print('<div id="fb-root"></div>');
}

?>