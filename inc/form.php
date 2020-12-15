<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 13 Dec 2020 */

/*
Determine whether to display the comment form which uses
PHP mail function (won't work on Windows)
#
WINDOWS constant set to TRUE or FALSE in settings.php
*/

// Declare variables
$close_comments = "";

if(!defined('ACCESS')) {
	die('Direct access not permitted to comment-form.php.');
}

// Form action

if (isset($_POST['submit'])) {

	$response = '';

	// Remove tags in email message
	$strip = array('<', '>');
	$_POST['name'] = str_replace($strip, ':', $_POST['name']);
	$comment = str_replace($strip, ':', $_POST['comment']);

	if (strlen($_POST['name']) < 1) {
		$problem = TRUE;
		$response .= TEXT17 . '<br>';
	}

	if (str_word_count($_POST['name']) > 2) {
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
		unset($comment);
		$response .= TEXT21 . '<br>';
	}

	$spam = array('a href=', '[url=', '[link=', 'http:');
	$spam_found = FALSE;

	foreach ($spam as $spamword) {
		if ((stripos($_POST['name'], $spamword) !== false) || (stripos($comment, $spamword) !== false)) {
			$spam_found = TRUE;
			break;
		}
	}

	if ($spam_found) {
		$problem = TRUE;
		unset($comment);
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

	// Block specified IP addresses
	$badIPs = array('76.164.219.139', '76.164.219.143');
	foreach ($badIPs as $spammer) {
		if ($spammer == $ip) {
			echo TEXT23;
			exit;
		}
	}

	// Dec 18 'stopwords' blocker
	if (file_exists(INC . 'stopwords.txt')) {
		$stopwordsArray = file(INC . 'stopwords.txt'); // Get stopword as array
		$string = stripslashes($comment);
		foreach ($stopwordsArray as $word) {
			if (stripos($string, trim($word)) !== FALSE) {
				// echo 'Stopword = ' . $word . "<br>";
				// echo "FOUND.<br>";
				$problem = TRUE;
				unset($string);
				unset($comment);
				$response .= TEXT48 . '<br>';
			}
		}
	}

	/* -------------------------------------------------- */

	$name = stripslashes($_POST['name']);
	$message = stripslashes($_POST['comment']);

	// Convert special (foreign) characters to normal text
	$text24 = html_entity_decode(TEXT24, ENT_QUOTES, "utf-8");
	$text26 = html_entity_decode(TEXT26, ENT_QUOTES, "utf-8");

	if (!$problem) {
		$showform = FALSE;
		$ip = $_SERVER['REMOTE_ADDR'];
		$comment = '';
		$subject = $text24 . ' ' . $email;
		$website = LOCATION;
		$headers = "Mime-Version: 1.0\r\n";
		$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
		$headers .= TEXT25 . " {$email}\r\n";
		$headers .= "Return-Path: {$email}\r\n";
		$body = $text26 . " {$name} [ {$email} ]\r\n\r\nWeb: {$website}\r\n\r\nPage: {$pageID}\r\n\r\nMessage: {$message}\r\n\r\nIP: {$ip}";
		$body = wordwrap($body, 70);
			if (mail(EMAIL, $subject, $body, $headers)) {
			$response .= TEXT27;
		} else {
			$response .= TEXT28;
		}
	} else {
		$comment = ($_POST['comment']);
		$response .= TEXT29;
	}
}

// End form action

if (isset($_POST['submit'])) {

	_print("
		<div class=\"response\" id=\"response\">

<hr>
<p>{$response}</p>

		</div>
	");

}

?>
<h6><?php _print(TEXT52); ?></h6>

<form method="post" class="contactform" action="#response" accept-charset="UTF-8">
<input type="text" name="name" size="22" value="<?php if (isset($_POST['submit'])) _print(strip_tags($_POST['name'])); ?>" maxlength="60" tabindex="1"><label for="name"><?php _print(TEXT32); ?></label><br>
<input type="text" name="email" size="22" value="<?php if (isset($_POST['submit'])) _print(strip_tags($_POST['email'])); ?>" maxlength="150" tabindex="2"><label for="email"><?php _print(TEXT33); ?></label><br>
<span class="zap"><input type="text" name="url" size="22" value="" maxlength="150" tabindex="3"><label>Leave this box empty</label></span>
<textarea name="comment" rows="8" placeholder="Your comment"><?php if (isset($_POST['submit'])) _print(strip_tags($comment)); ?></textarea>
<input type="submit" name="submit" class="submit" value="<?php _print(TEXT34); ?>">
</form>
