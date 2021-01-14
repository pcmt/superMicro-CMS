<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 14 Jan 2021 */

// Declare variables
$response = $response1 = "";

$thisAdmin = 'stopwords'; // For nav

require('./top.php');

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php

if (function_exists('p_title')) {
	p_title('stopwords');
} else {
	_print('Install the latest version of functions.php');
}

?></title>
<?php if (file_exists('../inc/settings.php')) { ?>
<link rel="shortcut icon" href="<?php _print(LOCATION); ?>favicon.ico">
<?php } ?>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="stylesheet.css" type="text/css">

</head>
<body>

<?php

/* -------------------------------------------------- */
// Start login

if (!$login) {
// Logged out

	if (!file_exists('./login-form.php')) {
		_print("Error. The file '/admin/login-form.php' does not exist. It must be installed.");
		exit();
	} else {
		require('./login-form.php');
	}

} elseif ($login) {

/* -------------------------------------------------- */
// Logged in

?>

<div id="wrap">

<h1><?php

	if (function_exists('h1')) {
		h1('stopwords');
	} else {
		_print('Install the latest version of functions.php');
	}

?></h1>

<?php

	if (file_exists('./nav.php')) {
		require('./nav.php');
	} else {
		_print("Error. The file '/admin/nav.php' does not exist. It must be installed.");
		exit();
	}

	$thefile = ('../inc/stopwords.txt');

	if (!file_exists($thefile)) {
		@touch($thefile);
		$response = '<em>The file <b>/inc/stopwords.txt</b> was just created.</em>';
	}

	// On page load
	$file_contents = file_get_contents($thefile);

	// Submit button pressed
	if (isset($_POST['pre-submit'])) {
		$response = "<em>You are about to edit <b>stopwords.txt</b> &raquo; click 'Update stopwords' again, or [ <a href=\"stopwords.php\" title=\"Abort\">abort</a> ]</em>";
	}

	// Submit button pressed again: write the file
	if (isset($_POST['submit'])) {
		$stopwords = stripslashes($_POST['content']);
		$fp = @fopen($thefile, 'w+');
		fwrite($fp, $stopwords);
		fclose($fp);
		$response = '<em>The file <b>stopwords.txt</b> was successfully edited.</em>';
	}

?>

<h3>Spam control stopwords for contact page</h3>

	<div id="response">

<?php

	_print('<p><span class="padded-multiline">');
	if (!$response) {
		_print('<em>No action requested. The current contents of <b>stopwords.txt</b> are shown.</em>');
	} else {
		_print($response);
	}
	_print('</span></p>');

?>

	</div>

<form action="<?php _print($self); ?>" method="post" accept-charset="UTF-8">

	<div id="boxes">

<label>Edit stopwords or phrases and update as required (each on a separate line, with no empty lines).</label>
<textarea name="content" cols="90" rows="21">
<?php

	if (isset($_POST['pre-submit'])) { // First press
		_print($_POST['content']);
	} elseif (isset($_POST['submit'])) { // Second press
		_print($_POST['content']);
	} else {
		_print($file_contents);
	}

?>
</textarea>

	</div>

	<div id="buttons">

<input type="submit" name="<?php

	if (!isset($_POST['pre-submit'])) {
		_print('pre-submit');
	} else {
		_print('submit');
	}

?>" value="Update 'stopwords'">
<input type="submit" name="submit2" class="fade" value="Get current">

	</div>

</form>

<?php

	include('./footer.php');
} else {

	/* -------------------------------------------------- */
	// No $login or !$login

	_print('<p>Login could not be verified.</p>');
}

?>

</div>

</body>
</html>