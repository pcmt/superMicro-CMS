<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 28 Jan 2023 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to top.php');
}

// Declare variables
$status = $notice = $user = $dofooter = $login = $domain = $secure = $secure_cookie = $path = $siteID = $self = '';

/* -------------------------------------------------- */
// For footer.php

global $tm_start;
$tm_start = 0;
$tm_start = array_sum(explode(' ', microtime()));

/* -------------------------------------------------- */
// Stuff

// Load functions
include('./functions.php');

/* -------------------------------------------------- */
// setup.php edits /inc/settings.php

if (file_exists('../inc/settings.php')) {
	include('../inc/settings.php');
}

if (defined('SITE_ID')) {
	$siteID = SITE_ID;
} else {
	$siteID = 'x';
}

$adminlink = "adminlink_{$siteID}";
$login_cookie_name = "superMicro_{$siteID}";

// Set cross-platform PHP_EOL constant (for 'explode') for backwards compatibility,
// otherwise is automatically set for the operating system the script is running on
if (!defined('PHP_EOL')) {
	switch (strtoupper(substr(PHP_OS, 0, 3))) {
		// Windows
		case 'WIN':
		define('PHP_EOL', "\r\n");
		break;

		// Mac
		case 'DAR':
		define('PHP_EOL', "\r");
		break;

		// Unix
		default:
		define('PHP_EOL', "\n");
	}
}

/* 'isWritable' function removed 04 Dec 20 */

/* -------------------------------------------------- */
// Login form action fix for empty PHP_SELF (all admin)

if (function_exists('phpSELF')) {
	$self = htmlspecialchars(phpSELF(), ENT_QUOTES, "utf-8");
} elseif (isset($_SERVER['PHP_SELF'])) {
	$self = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, "utf-8");
} else {
	$self = '';
}

/* -------------------------------------------------- */
// Logging in and out

// Set things up

if (file_exists('./password.php')) {
	include('./password.php');
}

/* ================================================== */
/* TO START WITH */

// For if logged in (logout sets address bar 'status' as 'logout')
if (isset($_GET['status'])) {
	$status = $_GET['status'];
}

$sent = 'tempVal_1';
$allowed = 'tempVal_2';

/* ================================================== */
/* Password submitted */

// Check 'user'
// Check salted and hashed submitted password against file version
// Set login and cookie only if checks pass

if (isset($_POST['submit0'])) {

	$allowed = array();
	$allowed[] = 'form';
	$allowed[] = 'password';
	$allowed[] = 'submit0';

	$sent = array_keys($_POST);
	if ($sent !== $allowed) {
		$user = 'unverified';
	}

	// Do the same process as hashing the original password and salt.
	// The [salted and hashed] password is contained $sh_password and
	// the salt is contained in $salt (both in password.php)

	$strip = array("<", ">", "\"", "'", "\\");

	$string1 = trim($_POST['password']);
	$string1 = str_replace($strip, '', $string1);
	$string1 = stripslashes($string1);

	$string2 = $salt;
	$string2 = str_replace($strip, '', $string2);
	$string2 = stripslashes($string2);

	$fullstring = $string1 . $string2;
	$sh_submitted = hash('sha256', $fullstring);

	// If form ok, login
	// Submitted password is hashed and must match $sh_password in password.php
	// (plus the user must be verified)
	if (($sh_submitted == $sh_password) && ($user !== 'unverified') && !$dofooter) {

		$login = TRUE; // $login is picked up in all the admin pages

		// Set adminlink cookie moved to bottom to refresh every admin click
		// Set password cookie to avoid repeated logins (checked in normal running)
		// Check test cookie
		if (isset($_COOKIE["supermicro_test_cookie"])) { // Cookies are working
			// Update the login cookie
			if ($secure_cookie) {
				setcookie($login_cookie_name, $sh_submitted, time() + (60*60*24*1), $path, $domain, 1, 1); // 1 day
			} else {
				setcookie($login_cookie_name, $sh_submitted, time() + (60*60*24*1), $path); // 1 day
			}
		} else { // Cookies aren't working
			$notice = "\n<h3><em>Test cookie not found.</em></h3>\n<p>Are cookies enabled?</p>\n";
		}

	} else { // Something else is wrong
		$login = FALSE;
		$notice = '<em>Invalid. Try again.</em>';
	}

}

/* ================================================== */
/* Normal running, nothing submitted */

// Get admin directory for cookie path
$path = basename(dirname(__FILE__)); // One level up

// Section to enable secure login cookie

// Try to get the domain
if (!empty($_SERVER['HTTP_HOST'])) {
	$domain = $_SERVER['HTTP_HOST'];
} elseif (!empty($_SERVER['SERVER_NAME'])) {
	$domain = $_SERVER['SERVER_NAME'];
} else {
	$domain = FALSE;
}

// Try to establish whether SSL or not
if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
	$secure = TRUE;
} else {
	$secure = FALSE;
}

if ($domain && $secure) {
	$secure_cookie = TRUE;
} else {
	$secure_cookie = FALSE;
}

// End of section

// Attempt to set test cookie each time any admin page is accessed
// Value = current time, duration 1 hour, after which notice given
// This cookie was also set by install.php with value "installation"

if ($secure_cookie) { // Secure
	setcookie("supermicro_test_cookie", "installed_sec", time() + 3600, "/", $domain, 1, 1); // One hour
} else { // Not secure
	setcookie("supermicro_test_cookie", "installed_ins", time() + 3600, "/"); // One hour.
}

// Check test cookie
if (isset($_COOKIE["supermicro_test_cookie"])) {
	$notice = FALSE; // if($notice) is in every admin page when not logged in
} else {
	$notice = "\n<h3><em>Are cookies enabled?</em></h3>\n<p>Site admin uses cookies.</p>\n";
}

// Check if a login cookie exists
if (isset($_COOKIE[$login_cookie_name])) {

	// Check if its value matches the submitted password
	// If it does, stay logged in, otherwise no login
	if ($_COOKIE[$login_cookie_name] == $sh_password) {
		$login = TRUE;
	} else {
		$login = FALSE;
	}

}

/* ================================================== */
/* Logout, or password submitted by unverified 'user' */

if (($status == 'logout') || (isset($_POST['submit0']) && ($user == 'unverified'))) {

	// Delete cookies
	if ($secure_cookie) {
		// Delete admin link cookie
		setcookie($adminlink, "loggedout_sec", time() - 3600, "/", $domain, 1, 1);
		// Delete password cookie
		setcookie($login_cookie_name, $sh_password, time() - 3600, $path, $domain, 1, 1);
	} else {
		setcookie($adminlink, "loggedout_ins", time() - 3600, "/");
		setcookie($login_cookie_name, $sh_password, time() - 3600, $path);
	}

	$login = FALSE;

}

if (!$login) {
	if (defined('LOCATION')) {
		$dofooter = TRUE;
	}
} else { // Logged in
	// Keep setting cookie only to show admin link in menu (expires after one hour)
	// Has to be in root to be accessible from pages (deleted on logout)
	if ($secure_cookie) {
		setcookie($adminlink, "loggedin_sec", time() + 3600, "/", $domain, 1, 1); // One hour. Root.
	} else {
		setcookie($adminlink, "loggedin_ins", time() + 3600, "/"); // One hour. Root.
	}
}

/* ================================================== */
/* Test feedback */
// echo '$path = ' . $path . '<br>';
/*
echo '<pre>';
print_r($_COOKIE);
echo '</pre>';
*/
?>