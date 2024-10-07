<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 04 Sept 2024 */
// Triple ===

if (!defined('ACCESS')) {
	die('Direct access not permitted to top.php');
}

// Declare variables
$notice = $user = $dofooter = $login = $domain = $secure = $secure_cookie = $path = $siteID = $self = $status = $form = $protocol = "";

/* -------------------------------------------------- */
// For footer.php

global $tm_start;
$tm_start = 0;
$tm_start = array_sum(explode(' ', microtime()));

/* -------------------------------------------------- */
// Files etc

include('./functions.php');

if (file_exists('../inc/settings.php')) { // May not exist
	include('../inc/settings.php');
}

if (file_exists('./password.php')) { // May not exist
	include('./password.php');
}

/* ================================================== */
/* TO START WITH, GET THE INFO */

// (1) Get admin directory for cookie path
$path = '/' . basename(dirname(__FILE__)) . '/'; // One level up

// (2) Try to get the domain
if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
	// Use the forwarded host when the request is through a proxy or load balancer
	$domain = $_SERVER['HTTP_X_FORWARDED_HOST'];
} elseif (!empty($_SERVER['HTTP_HOST'])) {
	// Use the standard HTTP host when no proxy is involved
	$domain = $_SERVER['HTTP_HOST'];
} elseif (!empty($_SERVER['SERVER_NAME'])) {
	// Fallback to SERVER_NAME if HTTP_HOST and HTTP_X_FORWARDED_HOST are not available
	$domain = $_SERVER['SERVER_NAME'];
} else {
	// If none of the variables are set, domain detection has failed
	$do_setup = FALSE;
	$response2 = '<em>Problem: site domain not detected.</em>';
}

// (3) Get protocol (https or http) otherwise don't setup

if (function_exists('get_protocol')) { // Function added 21 Nov 18
	$protocol = get_protocol() ? 'https://' : 'http://'; // See functions.php
	// Check it's returned one or the other
	// if (($protocol == 'https://') || ($protocol == 'http://')) {
		// echo 'get_protocol function works'; // For testing only
	// }
} else { // If function doesn't exist
	if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
		$protocol = 'https://';
	} else {
		$protocol = 'http://';
	}
}

if ($protocol == " ") {
	$do_setup = FALSE;
	$response3 = '<em>Problem: https or http not detected.</em>';
}

// (4) Type of cookie
if ($domain && $secure) {
	$secure_cookie = TRUE;
} else {
	$secure_cookie = FALSE;
}

// (5) Try to get the site ID

if (defined('SITE_ID')) {
	$siteID = SITE_ID;
} else if (file_exists('./siteid.txt')) {
	$siteID = trim(file_get_contents('./siteid.txt'));
} else {
	$siteID = FALSE;
	$notice = "\n<h3><em>No site ID.</em></h3>\n";
}

// (6) Cookie name
$loggedin = "loggedin_{$siteID}";
$adminlink = "adminlink_{$siteID}";

// (7)
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

// (8) Login form action fix for empty PHP_SELF (all admin)

if (function_exists('phpSELF')) {
	// If the function phpSELF exists, call it and sanitize the result
	$self = htmlspecialchars(phpSELF(), ENT_QUOTES, "utf-8");
} elseif (isset($_SERVER['PHP_SELF'])) {
	// If phpSELF does not exist, use $_SERVER['PHP_SELF'] and sanitize the result
	$self = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, "utf-8");
} else {
	// If neither method is available, set $self to an empty string
	$self = '#';
}

// (9)
// Attempt to set test cookie each time any admin page is accessed
// Value = current time, duration 1 hour, after which notice given
// This cookie was also set by install.php with value "installation"

if (!isset($_COOKIE["supermicro_test_cookie"])) {
	if ($secure_cookie) { // Secure
		setcookie("supermicro_test_cookie", "installed_sec", time() + 3600, "/", $domain, 1, 1); // One hour
	} else { // Not secure
		setcookie("supermicro_test_cookie", "installed_ins", time() + 3600, "/"); // One hour.
	}
}

// Check test cookie
if (isset($_COOKIE["supermicro_test_cookie"])) {
	$notice = FALSE; // if($notice) is in every admin page when not logged in
} else {
	$notice = "\n<h3><em>Are cookies enabled?</em></h3>\n<p>Site admin uses cookies.</p>\n";
}

/* ================================================== */
/* LOGIN STATUS (required for admin pages) */

// Check if a login cookie exists
if (isset($_COOKIE[$loggedin]) && ($_COOKIE[$loggedin] === $siteID)) {
	$login = TRUE;
} else {
	$login = FALSE;
}

/* ================================================== */
/* LOGIN SUBMITTED */

// Check salted and hashed submitted password against file version

if (isset($_POST['submit0']) && ($_SERVER['REQUEST_METHOD'] === 'POST')) {

	// To check hidden field
	$form = sanitizeIt($_POST['form']);

	// Do the same process as hashing the original password and salt.
	// The [salted and hashed] password is contained $sh_password and
	// the salt is contained in $salt (both in password.php)

	$string1 = sanitizeIt($_POST['password']);
	$string2 = sanitizeIt($salt);
	$fullstring = $string1 . $string2;
	$sh_submitted = hash('sha256', $fullstring);

	// If form ok, login
	// Submitted password is hashed and must match $sh_password in password.php
	if (($sh_submitted === $sh_password) && ($form === 'login')) {

		$login = TRUE; // $login is picked up in all the admin pages

		if (isset($_COOKIE["supermicro_test_cookie"])) { // Cookies are working
			// Update the cookies
			if ($secure_cookie) { // Ideally would be in admin folder
				setcookie($loggedin, $siteID, time() + 86400, "/", $domain, 1, 1); // One day
				setcookie($adminlink, $siteID, time() + 86400, "/", $domain, 1, 1); // One day
			} else {
				setcookie($loggedin, $siteID, time() + 86400, "/"); // One day
				setcookie($adminlink, $siteID, time() + 86400, "/"); // One day
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
/* LOGOUT */

if(isset($_GET['status'])){
	$status = $_GET['status'];
}

if ($status === 'logout') {

	$login = FALSE;

	// Delete cookies
	if ($secure_cookie) {
		// Delete admin link cookie
		if (isset($_COOKIE[$loggedin])) {
			setcookie($loggedin, FALSE, time() - 3600, "/", $domain, 1, 1);
			unset($_COOKIE[$loggedin]);
		}
		if (isset($_COOKIE[$adminlink])) {
			setcookie($adminlink, FALSE, time() - 3600, "/", $domain, 1, 1);
			unset($_COOKIE[$adminlink]);
		}
	} else {
		if (isset($_COOKIE[$loggedin])) {
			setcookie($loggedin, FALSE, time() - 3600, "/");
			unset($_COOKIE[$loggedin]);
		}
		if (isset($_COOKIE[$adminlink])) {
			setcookie($adminlink, FALSE, time() - 3600, "/");
			unset($_COOKIE[$adminlink]);
		}
	}

}

if (!$login) {
	if (defined('LOCATION')) {
		$dofooter = TRUE;
	}
}

/* ================================================== */
/* Test feedback */
/*
echo '$login = ' . $login . '<br>';
echo '$status = ' . $status . '<br>';
echo '$domain = ' . $domain . '<br>';
echo '$path = ' . $path . '<br>';
echo '$secure_cookie = ' . $secure_cookie . '<br>';
echo '$login_cookie_name = ' . $login_cookie_name . '<br>';
*/
#echo '<pre>';
#print_r($_COOKIE);
#echo '</pre>';

?>