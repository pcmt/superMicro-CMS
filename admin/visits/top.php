<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 25 Jan 2021 */

// No PHP errors detected in testing so
// normally leave error reporting off
error_reporting(0);

// Report errors (none found)
// error_reporting(E_ALL);

define('ACCESS', TRUE); // All includes in /visits/

// Declare variables
$p_word = $error = "";

session_start();

if (isset($_POST['submit_pass']) && $_POST['pass']) {

	$p_word = trim($_POST['pass']);

	// At least some letters + (optional) numbers, underscore and dash
	if (!preg_match('/^[a-zA-Z]+[a-zA-Z\-0-9._]+$/', $p_word)) {
		$error = '<p class="pword">Invalid character(s)</p>';
		$_SESSION['password'] = FALSE;
	} else {
		if ($p_word !== "v") { // As index.php and list.php
			$error = '<p class="pword">Wrong Password</p>';
			$_SESSION['password'] = FALSE;
		} else {
			$_SESSION['password'] = $p_word;
		}
	}

}

if (file_exists('../../inc/settings.php')) {
	require('../../inc/settings.php');
} else {
	die('Error in /admin/visits/top.php: /inc/settings.php not found');
}

if (file_exists('../../inc/functions.php')) {
	require('../../inc/functions.php');
} else {
	die('Error in /admin/visits/top.php: /inc/functions.php not found');
}

/* Safe 'self' function (from /admin/functions.php) */

function phpSELF() {
	// Convert special characters to HTML entities
	$str = htmlspecialchars($_SERVER['PHP_SELF']);
	if (empty($str)) { // Fix empty PHP_SELF
		// Strip query string
		$str = preg_replace("/(\?.*)?$/", "", $_SERVER['REQUEST_URI']);
	}

	return $str;
}

$self = htmlspecialchars(phpSELF(), ENT_QUOTES, "utf-8");

/* End of safe 'self' */

if (isset($_POST['page_logout'])) {
	unset($_SESSION['password']);
}

/* Get the path to the installation */
if (defined('LOCATION')) {
	$site = LOCATION;
} else {
	$site = "LOCATION not found";
}

?>
