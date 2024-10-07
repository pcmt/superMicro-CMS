<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 25 June 2024 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to login-form.php');
}

?>

<h1 id="pp">Password Protected</h1>

		<div id="pw">

<?php

if ($error) {
	_print_nlb("<p>{$error}</p>");
} else {
	_print_nlb("<p>Enter password:</p>");
}

_print_nlb('

<form method="post" class="pw" action="#" id="login">
<input type="password" name="pass">
<input type="submit" class="submit" name="submit_pass" value="' . TEXT14 . '">
</form>

');

?>
		</div>
