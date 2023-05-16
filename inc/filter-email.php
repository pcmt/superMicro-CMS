<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 30 April 2023 */

	if (!defined('ACCESS')) {
		die('Direct access not permitted to filter-email.php');
	}

	/* -------------------------------------------------- */
	// Remove stuff

	$name = preg_replace("/[^a-zA-Z0-9\s]/", "", trim($_POST['name']));
	$strip = array('<', '>');
	$comment = str_replace($strip, '', $_POST['comment']);

	if (strlen($name) < 1) {
		$problem = TRUE;
		$response .= TEXT17 . '<br>';
	}

	if (str_word_count($name) > 2) {
		$problem = TRUE;
		$response .= TEXT18 . '<br>';
	}

	$email = stripslashes(trim($_POST['email']));
	if ((strlen($email) < 5) || (!filter_var($email, FILTER_VALIDATE_EMAIL))) {
		$problem = TRUE;
		$response .= TEXT19 . '<br>';
	}

	if (strlen($comment) < 1) {
		$problem = TRUE;
		$response .= TEXT20 . '<br>';
	}

	/* -------------------------------------------------- */
	// Various attempts to block spam (may change)

	if ($_POST['url'] != '') {
		$problem = TRUE;
		if (isset($comment)) {
			$comment = '';
		}
		$response .= TEXT21 . '<br>';
	}

	$spam = array('a href=', '[url=', '[link=', 'http:');
	$spam_found = FALSE;
	foreach ($spam as $spamword) {
		if ((stripos($name, $spamword) !== false) || (stripos($comment, $spamword) !== false)) {
			$spam_found = TRUE;
			break;
		}
	}
	if ($spam_found) {
		$problem = TRUE;
		if (isset($comment)) {
			$comment = '';
		}
		$response .= TEXT22 . '<br>';
	}

	// Make sure the form was posted from a browser
	if (!isset($_SERVER['HTTP_USER_AGENT'])) {
		echo TEXT23;
		exit;
	}

	// Make sure the form was indeed POSTed
	if ((!$_SERVER['REQUEST_METHOD'] == "POST") || ("POST" != getenv('REQUEST_METHOD'))) {
		echo TEXT23;
		exit;
	}

	// Attempt to defend against header injections
	$badStrings = array('Content-Type:', 'MIME-Version:', 'Content-Transfer-Encoding:', 'bcc:', 'cc:');
	foreach ($_POST as $k => $v) {
		foreach ($badStrings as $v2) {
			if (stripos($v, $v2) !== false) {
				header('HTTP/1.0 403 Forbidden');
				exit;
			}
		}
	}

	/* -------------------------------------------------- */
	// Stopwords blocker

	if (file_exists(INC . 'stopwords.txt')) {
		$stopwordsArray = file(INC . 'stopwords.txt'); // Get stopword as array
		$string = stripslashes($comment);
		foreach ($stopwordsArray as $word) {
			$regex = '/\b'. $word .'\b/';
			if (preg_match($regex, $string, $matches, PREG_OFFSET_CAPTURE)) {
				$problem = TRUE;
				unset($string);
				unset($comment);
				$response .= TEXT48 . '<br>';
			}
		}
	}

?>