<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 13 Dec 2020 */

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Declare variables
$ip = $problem = NULL;

if (isset($_COOKIE['adminlink'])) {
	$admin = TRUE;
} else {
	$admin = FALSE;
}

$pageID = 'e'; // For menu.php (normally comes from html.php)

define('ACCESS', TRUE);

$time = microtime();
$time = explode(' ', $time);
$starttime = $time[1] + $time[0];

$showform = TRUE;

// Define absolute path to /inc/ folder (as in page.class.php)
$_inc = str_replace('\\', '/', dirname(__FILE__)) . '/inc/';
define('INC', $_inc);

if (file_exists(INC . 'error-reporting.php')) {
	require(INC . 'error-reporting.php');
} else {
	echo 'Error. Please install the file /inc/error-reporting.php';
	exit();
}

// Next bit in top.php but not loaded here
if ((APACHE == FALSE) || (!file_exists('./.htaccess'))) {
	$rewrite = FALSE;
} else {
	$rewrite = TRUE; // When not WINDOWS and .htaccess exists
}

if (file_exists(INC . 'lang.php')) {
	require(INC . 'lang.php');
} else {
	echo 'Error. Please install the file /inc/lang.php';
	exit();
}

/* -------------------------------------------------- */
// Contact form submitted

if (isset($_POST['submit'])) {

	$response = '';

	// Remove tags in email message
	$strip = array('<', '>');
	$_POST['name'] = str_replace($strip, ':', $_POST['name']);
	$_POST['comment'] = str_replace($strip, ':', $_POST['comment']);

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

	if (strlen($_POST['comment']) < 1) {
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
		if ((stripos($_POST['name'], $spamword) !== false) || (stripos($_POST['comment'], $spamword) !== false)) {
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
		$string = stripslashes($_POST['comment']);
		foreach ($stopwordsArray as $word) {
			if (stripos($string, trim($word)) !== FALSE) {
				// echo 'Stopword = ' . $word . "<br>";
				// echo "FOUND.<br>";
				$problem = TRUE;
				unset($string);
				unset($content);
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
		$body = $text26 . " {$name} [ {$email} ]\r\n\r\nWeb: {$website}\r\n\r\n{$message}\r\n\r\nIP: {$ip}";
		$body = wordwrap($body, 70);
			if (mail(EMAIL, $subject, $body, $headers)) {
			$response .= TEXT27;
		} else {
			$response .= TEXT28;
		}
	} else {
		$content = ($_POST['comment']);
		$response .= TEXT29;
	}
}

?>
<!DOCTYPE html>
<html<?php

if (defined('LANG_ATTR')) {
	_print(' lang="' . LANG_ATTR . '"');
}

if (defined('CONTACT_MENU') && (strlen(CONTACT_MENU) > 0)) {
	$contact_heading = CONTACT_MENU;
} else {
	$contact_heading = TEXT30;
}

?>>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php _print($contact_heading); ?></title>
<link rel="shortcut icon" href="favicon.ico">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<?php

include(INC . 'stylesheets.php');
_print("\n");

?>

</head>
<body>

<div id="wrap">

<?php include(INC . 'menu.php'); ?>

	<main id="content">

<h1><?php _print($contact_heading); ?></h1>

<?php

$phpMail = TRUE;
if (defined('APACHE')) {
	if (APACHE == FALSE) {
		$phpMail = FALSE;
	}
}

if (!$phpMail) { // Hides form and just shows email address

	$email = EMAIL;
	$parts = explode('@', $email);
	$user = $parts[0];
	$domain = $parts[1];
	$address = $user . '@' . $domain;

	$out = "<p><script>\n";
	$out .= 'var linktext = "' . $address . '";';
	$out .= "\n";
	$out .= 'var email1 = "' . $user . '";';
	$out .= "\n";
	$out .= 'var email2 = "' . $domain . '";';
	$out .= "\n";
	$out .= 'document.write("<a href=" + "mail" + "to:" + email1 + "@" + email2 + ">" + linktext + "<\/a>")';
	$out .= "\n</script></p>";

	_print($out);

} else { // Not Windows, show form

?>
<!-- H4 TEXT 31 removed //-->

<?php

	if (isset($_POST['submit'])) {
		_print_nlb('
		<div class="response">

<p>' . $response . '</p>

		</div>
		');
	}

	if ($showform) { // Don't hide form until message posted

?>

<p><?php _print(stripslashes(CONTACT_TEXT)); ?></p>

<form method="post" class="contactform" action="" accept-charset="UTF-8">
<input type="text" name="name" size="22" value="<?php if (isset($_POST['submit'])) _print(strip_tags($_POST['name'])); ?>" maxlength="60" tabindex="1"><label for="name"><?php _print(TEXT32); ?></label><br>
<input type="text" name="email" size="22" value="<?php if (isset($_POST['submit'])) _print(strip_tags($_POST['email'])); ?>" maxlength="150" tabindex="2"><label for="email"><?php _print(TEXT33); ?></label><br>
<span class="zap"><input type="text" name="url" size="22" value="" maxlength="150" tabindex="3"><label>Leave this box empty</label></span>
<textarea name="comment" rows="12" placeholder="Your comment"><?php if (isset($_POST['submit'])) _print(strip_tags($content)); ?></textarea>
<input type="submit" name="submit" class="submit" value="<?php _print(TEXT34); ?>">
</form>
<?php

	} // End $showform

} // End not Windows

?>

<p><i>&#8212;<?php _print(OWN_NAME); ?>&#8212;</i></p>

	</main>

<?php

include(INC . 'footer.php');

?>

</div>

</body>
</html>