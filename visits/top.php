<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 19 Dec 2020 */

// No PHP errors detected in testing so
// normally leave error reporting off
error_reporting(0);

// Report errors (none found)
// error_reporting(E_ALL);

define('ACCESS', TRUE); // All includes in /visits/

// Declare variables
$error = "";

session_start();

if (isset($_POST['submit_pass']) && $_POST['pass']) {

	$p_word = trim($_POST['pass']);

	if (preg_match('/[a-z_\-0-9]/i', $p_word)) {

		if ($p_word == "v") {
			$_SESSION['password'] = $p_word;
		} else {
			$error = "<p>Wrong Password</p>";
		}

	} else {
		$error = "<p>Invalid character(s)</p>";
	}

}

if (file_exists('../inc/settings.php')) {
	require('../inc/settings.php');
} else {
	die('Error. /inc/settings.php not found');
}

if (file_exists('../inc/functions.php')) {
	require('../inc/functions.php');
} else {
	die('Error. /inc/functions.php not found');
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
