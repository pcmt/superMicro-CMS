<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 29 July 2024 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to show-history.php');
}

// Declare variables
$to_omit = $skip = $found = $history = $history_array = $update = $a = $b = $c = $cookie_folder = "";

if (defined('SHOW_HISTORY') && (SHOW_HISTORY === TRUE)) {

	// Omit pages
	$to_omit = array('index', 'preview');
	$skip = FALSE;
	foreach ($to_omit as $found) {
		if ($found == $pageID) {
			$skip = TRUE;
		}
	}

	$smcms_history = 'x' . SITE_ID;

	// Get the history
	if (isset($_COOKIE[$smcms_history])) {

		$history = $_COOKIE[$smcms_history];
		// Whitespace changed to underscore (no whitespace in cookie values)
		// See corresponding 'explode' in history.php
		$historyArray = explode("_", $history); // Make array

		if (!$skip) {
			// If the current page is already in the cookie, do nothing
			// otherwise add it to the cookie for next page view.
			// If it is not in the cookie and the page is refreshed,
			// it will be added. The history will then show the current page.
			if (strpos($history, $pageID) === FALSE) {

				// Number of values
				$num = count($historyArray);

				// The values (null coalescing operator)
				$a = $historyArray[0] ?? "";
				$b = $historyArray[1] ?? "";
				$c = $historyArray[2] ?? "";

				if ($pageID != 'index') { // Exclude home page
					// $update is the cookie value (no whitespace)
					$update = $pageID . '_' . $a . '_' . $b; // Add this page
				} else { // Is home page
					$update = $a . '_' . $b . '_' . $c; // Home page (do nothing)
				}

				// Update cookie with this page ID, $a and $b being moved along and $c dropped
				// $update may have a trailing underscore (rtrimmed in history.php)
				setcookie($smcms_history, trim($update), time() + 31556926, $cookie_folder); // One year
			}
		}

	} else {

		if (!$skip) {
			setcookie($smcms_history, $pageID, time() + 31556926, $cookie_folder); // One year
		}

	}
}
















?>