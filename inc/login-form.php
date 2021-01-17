<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 13 Dec 2020 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to login-form.php');
}

// See ppp.php for why logout form is not displayed (18 Jan 20)

_print_nlb('
<h1 id="pp">Password Protected</h1>

		<div id="pw">

<p>Enter password:</p>

<form method="post" class="pw" action="" id="login">
<input type="password" name="pass">
<input type="submit" class="submit" name="submit_pass" value="' . TEXT14 . '">
</form>

		</div>');

?>
