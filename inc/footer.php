<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 08 July 2024 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to footer.php');
}

// Declare variables
$protocol = "";

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

$output = '<p><a href="' . LOCATION . '">' . $anchor . '</a>';
$output .= ' &copy; ' . NAME . ' ' . date("Y");
$output .= '</p>';

_print_nlab($output);

?>

	</footer>

<?php

if (file_exists(INC . 'tracking.php')) {
	include(INC . 'tracking.php');
}

$time = microtime();
$time = explode(" ", $time);
$endtime = $time[1] + $time[0];
$servetime = ($endtime - $starttime);
$servetime = number_format((float)$servetime, 4, '.', '');
_print_nlab('<!-- Served in ' . $servetime . ' secs //-->');

?>
