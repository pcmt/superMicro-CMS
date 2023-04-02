<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 17 March 2023 */

define('ACCESS', TRUE);

// Declare variables
$ip = $problem = NULL;
$pageID = 'e';
$showform = TRUE;
$regex = "";

// Define absolute path to /inc/ folder (as in html.php)
$_inc = str_replace('\\', '/', dirname(__FILE__)) . '/inc/';
define('INC', $_inc);

require(INC . 'prelims.php');

/* -------------------------------------------------- */
// Contact form submitted

if (isset($_POST['submit'])) {

	$response = '';

	if (file_exists(INC . 'filter-email.php')) {
		include(INC . 'filter-email.php');
	} else {
		_print("Error in /e.php: '/inc/filter-email.php' could not be found.");
		exit();
	}

	/* -------------------------------------------------- */

	$name = stripslashes($name);
	$comment = stripslashes($comment);

	// Convert special (foreign) characters to normal text
	$text24 = html_entity_decode(TEXT24, ENT_QUOTES, "utf-8");
	$text26 = html_entity_decode(TEXT26, ENT_QUOTES, "utf-8");

	if (!$problem) {
		$showform = FALSE;
		$ip = $_SERVER['REMOTE_ADDR'];
		// $comment = '';
		$subject = $text24 . ' ' . $email;
		$website = LOCATION;
		$headers = "Mime-Version: 1.0\r\n";
		$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
		$headers .= TEXT25 . " {$email}\r\n";
		$headers .= "Return-Path: {$email}\r\n";
		$body = $text26 . " {$name} [ {$email} ]\r\n\r\nWeb: {$website}\r\n\r\n{$comment}\r\n\r\nIP: {$ip}";
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
?>

<p>To get in touch, feel free to send me an email. Privacy respected.</p>

<?php
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