<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 04 Oct 2023 (tracking removed) */

if (!defined('ACCESS')) {
	die('Direct access not permitted to footer.php');
}

// Declare variables
$protocol = $logged = "";
$tracking = FALSE;
$feedback = 'No feedback';

?>

	<footer>

<?php

if (isset($pageID)) { // Not set in e.php and s.php
	_print_nlb('<!-- Page ID = ' . $pageID . ' //-->');
}

if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
	$protocol = 'https://';
} else {
	$protocol = 'http://';
}

$anchor = str_replace($protocol, '', LOCATION);

$output2 = '<p><a href="' . LOCATION . '">' . $anchor . '</a>';
$output2 .= ' &copy; ' . NAME . ' ' . date("Y");
$output2 .= '</p>';

_print_nlab($output2);

?>

	</footer>

<?php

if ($admin) {
	$logged = 'Logged in';
} else {
	$logged = 'Logged out';
}

/* Visitor tracking ============================== */

/* Removed */

/* End visitor tracking ========================== */

$time = microtime();
$time = explode(" ", $time);
$endtime = $time[1] + $time[0];
$servetime = ($endtime - $starttime);
$servetime = number_format((float)$servetime, 4, '.', '');
_print_nlab('<!-- Served in ' . $servetime . ' secs //-->');

?>
