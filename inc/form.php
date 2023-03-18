<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 17 March 2023 */
/* Not quite the same as e.php */

/*
Determine whether to display the comment form which uses
PHP mail function (won't work on Windows)
#
WINDOWS constant set to TRUE or FALSE in settings.php
*/

// Declare variables
$comment = $close_comments = $problem = "";
// From e.php:
$ip = $problem = NULL;
$showform = TRUE;
$regex = "";

if(!defined('ACCESS')) {
	die('Direct access not permitted to form.php');
}

// Form action

if (isset($_POST['submit'])) {

	$response = '';

	if (file_exists(INC . 'filter-email.php')) {
		include(INC . 'filter-email.php');
	} else {
		_print("Error in /inc/form.php: '/inc/filter-email.php' could not be found.");
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
		$body = $text26 . " {$name} [ {$email} ]\r\n\r\nWeb: {$website}\r\n\r\nPage: {$pageID}\r\n\r\nMessage: {$comment}\r\n\r\nIP: {$ip}";
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

	_print_nlb('
		<div class="response" id="response">

<hr>

<p>' . $response . '</p>

		</div>

	');

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
