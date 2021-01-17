<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 18 Nov 2020 */

// top.php loads this file, but only if html.php detects a password
// (1) This file starts a session then:
// (2) checks whether both parts of the password form were submitted
// (3) checks the password for valid characters
// (4) compares the entered password with the one in the page (html.php)
// (5) if correct starts $_SESSION['password'] containing the password

if (!defined('ACCESS')) {
	die('Direct access not permitted to ppp.php');
}

// Declare variables
$error = NULL;

session_start();

if (isset($_POST['submit_pass']) && $_POST['pass']) {

	$p_word = trim($_POST['pass']);

	if (preg_match('/[a-z_\-0-9]/i', $p_word)) {

		if ($p_word == $password) {
			$_SESSION['password'] = $p_word;
		} else {
			$error = "<p>Wrong Password</p>";
		}

	} else {
		$error = "<p>Invalid character(s)</p>";
	}

}

// if (isset($_POST['page_logout'])) {
	// unset($_SESSION['password']);
// }

?>