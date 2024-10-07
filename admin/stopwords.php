<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 01 July 2024 */

define('ACCESS', TRUE);

// Declare variables
$response = $preclass = "";

$thisAdmin = 'stopwords'; // For nav

if (!file_exists('./top.php')) { // Leave this
	echo "Error. The file '/admin/<strong>top.php</strong>' does not exist.";
	exit();
}

include('./top.php'); // Loads functions.php

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php p_title('stopwords'); ?></title>
<?php includeFileIfExists('./icons.php'); ?>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="styles.css" type="text/css">

</head>
<body>

<?php

/* -------------------------------------------------- */
// Start login

if (!$login) {
// Logged out

	includeFileIfExists('./login-form.php');


} elseif ($login) {

/* -------------------------------------------------- */
// Logged in

?>

<div id="o"><div id="wrap">

<h1><?php h1('stopwords'); ?></h1>

<?php

	includeFileIfExists('./nav.php');

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

<h3>Spam control stopwords for messages</h3>

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

		<div class="textarea-container">

<textarea class="flexitem" name="content" cols="90" rows="21">
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

		</div><!-- end .textarea-container //-->

	</div>

	<div id="buttons">

		<div>

<input type="submit" name="<?php

	if (!isset($_POST['pre-submit'])) {
		_print('pre-submit');
		$preclass = '';
	} else {
		_print('submit');
		$preclass = 'class="pre" ';
	}

?>" <?php _print($preclass); ?>value="Add stopwords">
<input type="submit" name="submit2" class="fade" value="Get current">

		</div>

	</div>

</form>

<?php

	includeFileIfExists('./footer.php');


} else {

	/* -------------------------------------------------- */
	// No $login or !$login

	_print('<p>Login could not be verified.</p>');
}

?>
</div></div>

</body>
</html>