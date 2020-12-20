<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 18 Dec 2020 */

if (file_exists('./top.php')) {
	require('./top.php');
} else {
	die('Error. /visits/top.php not found');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>superMicro CMS visits</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Expires" content="Mon, 26 Jul 1997 05:00:00 GMT">
<meta http-equiv="Pragma" content="no-cache">
<link rel="stylesheet" href="stylesheet.css" type="text/css">
<meta name="robots" content="noindex,nofollow">

</head>

<body>

<div id="wrap">

<?php

if ( isset($_SESSION['password']) && $_SESSION['password'] == "v" ) {

?>

<!-- CONTENT (correct password entered) -->

<h1>Hits for <span><a href="<?php _print($site); ?>" target="_blank"><?php _print($site); ?></a></span></h1>

<?php

	if (file_exists('./delete.php')) {
		include('./delete.php');
	} else {
		die('Error. /visits/delete.php not found');
	}

?>

<form action="" method="post">
<input class="dark" type="submit" name="delete" title="Are you sure?" value="Delete"> <input class="light" type="submit" name="refresh" value="Refresh">
</form>
<!--
<form method="post" action="" id="logout">
<input type="submit" name="page_logout" value="Logout">
</form>
//-->

<hr>

<p>See also <a href="./" title="Page stats">page stats</a>&nbsp;&raquo;</p>
<?php

	$page = $self;
	$page = str_replace("index.php", "", $page);

	if (file_exists('./counts.php')) {
		include('./counts.php');
	} else {
		die('Error. /visits/counts.php not found');
	}

	$temp  = file_get_contents("tempcount.txt");
	_print_nlb('<p>The most recent 250 hits from <a href="https://supermicrocms.com/visitor-tracking" target="_blank">temporary count</a> of <strong>' . $temp . '</strong> (emptied at 1000):');

?>

	<div id="results">

<ol reversed>

<?php

	// Open the file for reading
	$file = 'listhits.txt';
	$fh = fopen($file, 'rb');

	// Loop a specified number of times
	for ($i = 0; $i < 250; $i++) {
		// Read a line
		$line = fgets($fh);

		// If a line was read then output it
		if ($line !== FALSE) {
			_print_nlb("<li>{$line}</li>\n");
		}
	}

	// Close the file handle
	fclose($fh);

?></ol>

	</div>

<p><a href="https://supermicrocms.com/" target="_blank">supermicrocms.com</a></p>

<!-- END OF CONTENT -->

<?php } else { ?>

<form method="post" action="">
<input type="password" name="pass">
<input class="password" type="submit" name="submit_pass" value="Submit">
</form>

<?php _print($error); } ?>

</div>

</body>
</html>
