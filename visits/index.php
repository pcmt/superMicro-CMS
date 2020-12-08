<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 01 Dec 2020 */

// No PHP errors detected in testing so
// normally leave error reporting off
error_reporting(0);
ini_set('display_errors', 0);

// Report errors (none found)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Declare variables
$error = "";

session_start();

if (isset($_POST['submit_pass']) && $_POST['pass']) {

	$p_word = trim($_POST['pass']);

	if (preg_match('/[a-z_\-0-9]/i', $p_word)) {

		if ($p_word == "v") {
			$_SESSION['password'] = $p_word;
		} else {
			$error = "<p>Wrong Password</p>";
		}

	} else {
		$error = "<p>Invalid character(s)</p>";
	}

}

define('ACCESS', TRUE);
if (file_exists('../inc/settings.php')) {
	require('../inc/settings.php');
} else {
	die('Error. /inc/settings.php not found');
}

/* Safe 'self' function (from /admin/functions.php) */

function phpSELF() {
	// Convert special characters to HTML entities
	$str = htmlspecialchars($_SERVER['PHP_SELF']);
	if (empty($str)) { // Fix empty PHP_SELF
		// Strip query string
		$str = preg_replace("/(\?.*)?$/", "", $_SERVER['REQUEST_URI']);
	}

	return $str;
}

$self = htmlspecialchars(phpSELF(), ENT_QUOTES, "utf-8");

/* End of safe 'self' */

if (isset($_POST['page_logout'])) {
	unset($_SESSION['password']);
}

/* Get the path to the installation */
if (defined('LOCATION')) {
	$site = LOCATION;
} else {
	$site = "LOCATION not found";
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

<h1>Hits for <span><a href="<?php echo $site; ?>" target="_blank"><?php echo $site; ?></a></span></h1>

<?php

if (isset($_POST['delete'])) { // Deletes everything

	$the_date = date('l jS F Y H:i:s'); // For listhits.txt
	$since_date = $the_date;
	$time = time();

	$fp1 = fopen("count.txt", "w") or die("Error!");
	if ($fp1) {
		echo "count.txt emptied<br>\n";
	}
	fwrite($fp1, "0");
	fclose($fp1);

	$fp2 = fopen("listhits.txt", "w") or die("Error!");
	if ($fp2) {
		echo "listhits.txt emptied<br>\n";
	}
	fwrite($fp2, "");
	fclose($fp2);

	$fp3 = fopen("since.txt", "w") or die("Error!");
	if ($fp3) {
		echo "since.txt updated<br>\n\n";
	}
	fwrite($fp3, $time);
	fclose($fp3);

	$fp4 = fopen("tempcount.txt", "w") or die("Error!");
	if ($fp4) {
		echo "tempcount.txt emptied<br><br>\n";
	}
	fwrite($fp4, "0");
	fclose($fp4);

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

<?php

$page = $self;
$page = str_replace("index.php", "", $page);

$visits  = file_get_contents("count.txt");
$since  = file_get_contents("since.txt"); // Timestamp
$formatsince = date('l jS F Y H:i:s', $since);
echo "<p>Total hits since delete: <strong>{$visits}</strong> since {$formatsince}</p>\n\n";

// Calculate hits per day
$start = strtotime(date('Y-m-d', $since));
$end = strtotime(date('Y-m-d', time()));

// $days_between = ceil(abs($end - $start) / 86400);
$days_between = (($end - $start) / 86400);
$dayscounted = $days_between + 1;
$hitsperday = ceil(abs($visits / $dayscounted));
echo '<p>Days counted since delete: <strong>' . $dayscounted . '</strong>, so <strong>' . $hitsperday . '</strong> hits per day on average</p>';

$temp  = file_get_contents("tempcount.txt");
echo "<p>The most recent 250 hits from <a href=\"https://supermicrocms.com/visitor-tracking\" target=\"_blank\">temporary count</a> of <strong>{$temp}</strong> (emptied at 1000):\n\n";

?>

	<div id="results">

<ol reversed><?php

echo "\n\n";

// Open the file for reading
$file = 'listhits.txt';
$fh = fopen($file, 'rb');

// Loop a specified number of times
for ($i = 0; $i < 250; $i++) {
	// Read a line
	$line = fgets($fh);

	// If a line was read then output it
	if ($line !== false) {
		echo "<li>{$line}</li>\n\n";
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

<?php echo $error; } ?>

</div>

</body>
</html>
