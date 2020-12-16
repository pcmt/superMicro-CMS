<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 16 Dec 2020 */

// No PHP errors detected in testing so
// normally leave error reporting off
error_reporting(0);
ini_set('display_errors', 0);

// Report errors (none found)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Declare variables
$error = $num = "";

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

if (file_exists('../inc/functions.php')) {
	require('../inc/functions.php');
} else {
	die('Error. /inc/functions.php not found');
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

<title>superMicro CMS stats</title>
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

<h1>Page stats for <span><a href="<?php _print($site); ?>" target="_blank"><?php _print($site); ?></a></span></h1>

<form action="" method="post">
<input class="light" type="submit" name="refresh" value="Refresh">
</form>
<!--
<form method="post" action="" id="logout">
<input type="submit" name="page_logout" value="Logout">
</form>
//-->

<hr>

<p>See also <a href="./">list of hits</a>&nbsp;&raquo;</p>
<?php

$visits = file_get_contents("count.txt");
$since = file_get_contents("since.txt"); // Timestamp
$formatsince = date('l jS F Y H:i:s', (int)$since);
_print_nlb('<p>Page totals since delete: <strong>' . $visits . '</strong> since ' . $formatsince . '</p>');

// Calculate hits per day
$start = strtotime(date('Y-m-d', (int)$since));
$end = strtotime(date('Y-m-d', time()));

// $days_between = ceil(abs($end - $start) / 86400);
$days_between = (($end - $start) / 86400);
$dayscounted = $days_between + 1;
$hitsperday = ceil(abs($visits / $dayscounted));
_print_nlb('<p>Days counted since delete: <strong>' . $dayscounted . '</strong>, so <strong>' . $hitsperday . '</strong> hits per day on average</p>');

?>

<p>Pages and number of hits:</p>

	<div id="results">

<ol>

<?php

	// Identify each pageID and count the number of times it appears in the list
	// Get text files into array
	$pageidArray = file('pageid.txt');
	$list = array_count_values($pageidArray);
	arsort($list, SORT_NUMERIC);
	foreach ($list as $page => $num) {
		$page = str_replace("\n", "", trim($page));
		// $anchor = $page;
		if (!APACHE) { // Add .php extension
			$page = $page . '.php';
		} elseif ($page == 'index') {
			$page = str_replace("index", "", $page);
		}
		_print_nlb('<li><a href="' . LOCATION . $page . '" target="_blank"><span>' . LOCATION .'</span>' . $page . '</a> : ' . $num .'</li>');
	}

	_print_nlab('<br>');

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
