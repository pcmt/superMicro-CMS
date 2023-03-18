<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 16 March 2023 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to login-form.php');
}

?>

<h1 id="pp">Password Protected</h1>

<?php

if ($error) {
	_print_nlb("{$error}");
}

_print_nlb('

		<div id="pw">

<p>Enter password:</p>

<form method="post" class="pw" action="" id="login">
<input type="password" name="pass">
<input type="submit" class="submit" name="submit_pass" value="' . TEXT14 . '">
</form>

		</div>');

?>
