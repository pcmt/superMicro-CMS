<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 06 Oct 2020 */

// Declare variables
$response = $response1 = "";

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
<link rel="shortcut icon" href="<?php echo LOCATION; ?>favicon.ico">
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

?>
	<div id="loginform">

<h1>superMicro CMS <i>login</i></h1>

<?php

	if ($notice) {
		echo "\n" . $notice . "\n"; // From top.php (cookie test response)
	}

?>

<form id="pw" action="<?php echo $self; ?>" method="post">
<label><b>Enter password:</b></label>
<input type="hidden" name="form" value="login">
<input type="password" name="password" size="25" maxlength="32">
<input type="submit" name="submit0" value="Submit Password">
</form>

<?php

	if ($response) {
		echo '<p><em>' . $response . '</em></p>'; // If the user didn't do something
		echo "\n";
	}

	// Footer link etc
	if (function_exists('loggedoutFooter')) {
		// Prints link to home page if 'dofooter' + lost/forgotten password link if logged out
		loggedoutFooter();
	} else {
		echo "\n";
		echo '<p>Missing function. Install the latest version of <strong>superMicro CMS</strong>.</p>';

	}

	echo "\n";

?>

	</div>

<?php

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

<p id="nav"><a href="<?php echo LOCATION; ?>">&#171;&nbsp;Site</a> 
<a href="./index.php" title="Create/edit/delete pages">Pages</a> 
<a href="./images.php" title="Upload or delete images">Images</a> 
<a href="./htaccess.php" title="Create .htaccess file">.htaccess</a> 
<a href="./backup.php" title="Backup">Backup</a> 
<a href="./setup.php" title="Setup">Setup</a> 
<a href="?status=logout" title="Logout">Logout</a> 
<a href="https://supermicrocms.com/information" title="Help" class="ext" target="_blank">Help&nbsp;&#187;</a></p>

<?php

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

	echo '<p><span class="padded-multiline">';

	if (!$response) {
		echo '<em>No action requested. The current contents of <b>stopwords.txt</b> are shown.</em>';
	} else {
		echo $response;
	}

	echo '</span></p>';

?>

	</div>

<form action="<?php echo $self; ?>" method="post" accept-charset="UTF-8">

	<div id="boxes">

<label>Edit stopwords or phrases and update as required (each on a separate line, with no empty lines).</label>
<textarea name="content" cols="90" rows="21">
<?php

	if (isset($_POST['pre-submit'])) { // First press
		echo $_POST['content'];
	} elseif (isset($_POST['submit'])) { // Second press
		echo $_POST['content'];
	} else {
		echo $file_contents;
	}

?>
</textarea>

	</div>

	<div id="buttons">

<input type="submit" name="<?php

	if (!isset($_POST['pre-submit'])) {
		echo 'pre-submit';
	} else {
		echo 'submit';
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

	echo '<p>Login could not be verified.</p>';
}

?>

</div>

</body>
</html>