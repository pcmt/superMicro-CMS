<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 07 Dec 2020 */

// Declare variables

if (!defined('ACCESS')) {
	die('Direct access not permitted to footer.php.');
}

if (isset($pageID)) { // Not set in e.php and s.php
	_print("\n<!-- Page ID = {$pageID} //-->\n\n");
}

?>
	<footer>

<?php

if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
	$protocol = 'https://';
} else {
	$protocol = 'http://';
}

$anchor = str_replace($protocol, '', LOCATION);

$output2 = '<p><a href="' . LOCATION . '">' . $anchor . '</a>';
$output2 .= ' &copy; ' . NAME . ' ' . date("Y");
$output2 .= '</p>';

_print("{$output2}\n");

/* Visitor tracking moved here from html.php 07 Dec 20 */
if (defined('TRACK_HITS') && TRACK_HITS) {
	if (file_exists(INC . 'tracking.php')) {
		include(INC . 'tracking.php');
	}
}

$time = microtime();
$time = explode(" ", $time);
$endtime = $time[1] + $time[0];
$servetime = ($endtime - $starttime);
$servetime = number_format((float)$servetime, 4, '.', '');
_print("\n<!-- Served in {$servetime} secs //-->\n");

?>

	</footer>
