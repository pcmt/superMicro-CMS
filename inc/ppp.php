<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 09 June 2024 */

// top.php loads this file, but only if html.php detects $password
// (1) This file starts a session then:
// (2) checks whether both parts of the password form were submitted
// (3) checks the password for valid characters
// (4) compares the entered password with the one in the page (html.php)

if (!defined('ACCESS')) {
	die('Direct access not permitted to ppp.php');
}

// Declare variables
$error = NULL;

session_start();

if (isset($_POST['submit_pass']) && $_POST['pass']) {

	$p_word = trim($_POST['pass']);

	if (!preg_match("/^[a-zA-Z0-9 ]*$/u", $p_word)) {
		$error = "<p>Invalid character(s). Letters and/or numbers only.</p>";
	} else {
		if ($p_word === $password) {
			$_SESSION['password'] = $p_word;
		} else {
			$error = "<p>Wrong password.</p>";
		}
	}

}

?>