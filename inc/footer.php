<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 30 Dec 2020 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to footer.php.');
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
if (defined('TRACK_HITS')) {

	if (TRACK_HITS && ($pageID != 'preview')) { // Previews disappear

		// (1) All hits are tracked
		$tracking = TRUE;

		// None of the following conditions can apply to outside visits
		// They apply only to the administrator and her/his visits because
		// Only the administrator can be $admin and set the cookie

		// (2) Logged in hits not tracked
		// 'Track my hits' = empty
		if ( (isset($admin) && $admin) && !isset($_COOKIE["track"]) ) {
			$tracking = FALSE;
		}

		// (3) Don't track any of my hits
		// 'Track my hits' = NO
		if (isset($_COOKIE["track"]) && ($_COOKIE["track"] == 'no')) {
			$tracking = FALSE;
		}

		// (4) Track all my hits
		// 'Track my hits' = YES
		if (isset($_COOKIE["track"]) && ($_COOKIE["track"] == 'yes')) {
			$tracking = TRUE;
		}

		// Start tracking hits
		if ($tracking) {
			if (file_exists(INC . 'tracking.php')) {
				include(INC . 'tracking.php');
			} else {
				$feedback = "Error in /inc/footer.php: '/inc/tracking.php' does not exist";
			}
		} else {
			$feedback = 'Not tracking';
		}

	} else {
		$feedback = 'Track hits not set';
	}

} else {
	$feedback = 'Error in /inc/footer.php: track hits not defined';
}

_print_nla('<!-- ' . $logged . ' //-->');
_print_nla('<!-- ' . $feedback . ' //-->');

/* End visitor tracking ========================== */

$time = microtime();
$time = explode(" ", $time);
$endtime = $time[1] + $time[0];
$servetime = ($endtime - $starttime);
$servetime = number_format((float)$servetime, 4, '.', '');
_print_nlab('<!-- Served in ' . $servetime . ' secs //-->');

?>
