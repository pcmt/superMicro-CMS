<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 01 April 2023 */
// This file is included in all admin pages

if (!defined('ACCESS')) {
	die('Direct access not permitted to login-form.php');
}

?>
<div id="loginform">

<h1>superMicro CMS <i>login</i></h1>

<?php

	if ($notice) {
		_print("\n" . $notice . "\n"); // From top.php (cookie test response)
	}

	if (!defined('SITE_ID')) {
		if (file_exists('siteid.txt')) {
			$siteID = file_get_contents('siteid.txt');
		} else {
			_print('<p>Site ID is not yet stored.</p>');
		}
	} else if (defined('SITE_ID')) {
		$siteID = SITE_ID;
	} else {
		$siteID = FALSE;
		_print('<p>Error. Site ID is not yet defined.</p>');
	}

?>

<form id="pw" action="<?php _print($self); ?>" method="post">
<label><b>Enter password:</b></label>
<input type="hidden" name="form" value="<?php _print($siteID); ?>">
<input type="password" name="password" size="25" maxlength="32">
<input type="submit" name="submit0" value="Submit Password">
</form>

<?php

	if ($response) {
		_print('<p><em>' . $response . '</em></p>'); // If the user didn't do something
		_print("\n");
	}

	// Footer link etc
	if (function_exists('loggedoutFooter')) {
		// Prints link to home page if 'dofooter' + lost/forgotten password link if logged out
		loggedoutFooter();
	} else {
		_print("\n");
		_print('<p>Error. Missing function <strong>loggedoutFooter</strong>.<br>Install the latest version of <strong>superMicro CMS</strong>.</p>');

	}

	_print("\n");

?>

</div>
