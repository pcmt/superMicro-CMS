<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 10 Feb 2023 */

if (!defined('ACCESS')) {
	die('Direct access not permitted to tracking.php');
}

// This file adds formatted hits to 'listhits.txt'
// to be read by /admin/visits/

// No PHP errors detected in testing so
// normally leave error reporting off
error_reporting(0);
// error_reporting(E_ALL);

// Declare variables
$blocked = $url = $escaped_url = $error = $info = "";

// Relative path from root to directory
$visits = ADMIN . '/visits/';
// _print_nlb('<!-- visits file: ' . $visits . ' //-->'); // NEVER EXPOSE THIS

// IP address
if (isset($_SERVER['REMOTE_ADDR'])) {
	$ip = $_SERVER['REMOTE_ADDR'];
	// IP host address
	if ($ip) {
		$ip_hostaddress = gethostbyaddr($ip); // Reverse DNS lookup
	} else {
		$ip_hostaddress = NULL;
	}
} else {
	$ip = 'No IP address';
}

?>

<!-- Google Analytics -->
<!-- Code here //-->

<?php

// Date
$the_date = date('l jS F Y H:i:s');
$time = time();

// User agent
if (isset($_SERVER['HTTP_USER_AGENT'])) {

	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	// Ignore some user agent strings (add manually):
	$ignore = array('crawl', 'spider', 'bot', 'slurp', 'archiver', 'indexer', 'python-requests', 'go-http', 'scrap', 'batch', 'facebookexternalhit', 'WhatsApp');
	foreach ($ignore as $val) {
		if (stripos($user_agent, $val) !== FALSE) {
			$blocked = TRUE;
		}
	}

	if ($ip_hostaddress) {
		// Ignore some IP host address strings (add manually):
		$ignore = array('members.linode.com', 'baiduspider', '119.', 'crawl', 'spider', 'probe.onyphe', 'clients.your-server.de');
		foreach ($ignore as $val) {
			if (stripos($ip_hostaddress, $val) !== FALSE) {
				$blocked = TRUE;
			}
		}
	}

} else { // If no user agent, stop here
	$user_agent = 'no user agent';
	$blocked = TRUE;
}

/* ============================================================ */
// Process the hit

if (!$blocked) {

	// Try to detect device
	if (stristr($user_agent, 'iPhone')) {
		$info = 'iPhone';
	} elseif (stristr($user_agent, 'iPad')) {
		$info = 'iPad';
	} elseif (stristr($user_agent, 'Android') || stristr($user_agent, 'Opera Mini')) {
		$info = 'Android';
	} elseif (stristr($user_agent, 'Windows')) {
		$info = 'Windows';
	} elseif (stristr($user_agent, 'Macintosh')) {
		$info = 'Macintosh';
	} elseif (stristr($user_agent, 'X11; CrOS')) {
		$info = 'Chrome desktop';
	} elseif (stristr($user_agent, 'Mobi')) {
		$info = 'Mobile';
	} elseif (stristr($user_agent, 'Ubuntu; Linux')) {
		$info = 'Ubuntu Linux';
	} elseif (stristr($user_agent, 'Linux')) {
		$info = 'only Linux detected';
	} elseif (stristr($user_agent, 'HttpClient')) {
		$info = 'HttpClient (GET, POST, proxy)';
	} else {
		$info = 'other';
	}

	// Page the script is on
	$page = $_SERVER['PHP_SELF'];

	// Page from which the current page was called
	if (isset($_SERVER['HTTP_REFERER'])) {
		$referer = $_SERVER['HTTP_REFERER'];
		if (strlen($referer) > 100) { // If over 100 chars
			$referer = substr($referer, 0, 100) . ' ...'; // Truncate
		}
	} else {
		$referer = 'No referrer';
	}

	if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
		$protocol = 'https://';
	} else {
		$protocol = 'http://';
	}

	$url =  "{$protocol}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
	$escaped_url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );

	// Build the data to display
	$hitinfo = 'Page: <a href="' . $escaped_url . '" target="_blank">' . $escaped_url . '</a><br>' . $the_date . ' [ <span class="info">' . $info . '</span> ]<br>IP: <span class="mute">https://ip-address.us/lookup/</span>' . $ip . '<br>Referrer: <span>' . $referer . '</span>';

	// Text file where hits counted
	$countfile = $visits . 'count.txt';
	// Text file where hits listed
	$hitsfile = $visits . 'listhits.txt';
	// Temporary text file where hits counted
	$temp_countfile = $visits . 'tempcount.txt';
	// Text file for tempcount.txt reset date/time
	$temp_count_reset = $visits . 'tempcountreset.txt';
	// Text file where pageID listed
	$pageidfile = $visits . 'pageid.txt';

	// Update total count
	// Nothing in this script resets the total count
	$fp1 = @fopen($countfile, 'c+');
	flock($fp1, LOCK_EX);
	$count = (int)fread($fp1, filesize($countfile));
	ftruncate($fp1, 0);
	fseek($fp1, 0);
	fwrite($fp1, $count + 1);
	flock($fp1, LOCK_UN);
	fclose($fp1);

	// Get temporary count
	$temp_count = file_get_contents($temp_countfile);

	// Prevent large files being written
	if ($temp_count > 999) {

		// Delete all hits and start again
		$fp3 = @fopen($hitsfile, "w");
		fwrite($fp3, "");
		fclose($fp3);

		// Do the same with the temporary count file
		$fp4 = @fopen($temp_countfile, "w");
		fwrite($fp4, "1"); // The hit which triggered the reset
		fclose($fp4);

		// Do the same with the page id file
		$fp5 = @fopen($pageidfile, "w");
		fwrite($fp5, "");
		fclose($fp5);

		// Store temporary count reset date/time
		$fp6 = @fopen($temp_count_reset, "w");
		fwrite($fp6, $time);
		fclose($fp6);

	} else {

		// Add 1 to temporary count
		$fp2 = @fopen($temp_countfile, 'c+');
		flock($fp2, LOCK_EX);
		$temp_count = (int)fread($fp2, filesize($temp_countfile));
		ftruncate($fp2, 0);
		fseek($fp2, 0);
		fwrite($fp2, $temp_count + 1);
		flock($fp2, LOCK_UN);
		@fclose($fp2);

	}

	/* Carry on regardless */

	// Add pageID to list
	// Total hits per page should add up to $temp_count
	if ($pageID) {
		$current = file_get_contents($pageidfile);
		// Append to the file
		$current .= $pageID . "\n";
		// Write the contents back to the file
		file_put_contents($pageidfile, $current);
		// For info
		_print_nlb('<!-- Tracking "/' . $pageID . '" //-->');
	}

	// Add hit details to top of listhits.txt
	$file_data = $hitinfo . "\n"; // New hit
	$file_data .= file_get_contents($hitsfile); // Add previous hits
	file_put_contents($hitsfile, $file_data); // Update list

} // End of 'if not blocked'

if ($error) {
	_print_nlb('<!-- Error in /inc/tracking.php: ' . $error . ' //-->');
}
if ($ip) {
	_print_nlb('<!-- IP ' . $ip . ' //-->');
} else {
	_print_nlb('<!-- No ip //-->');
}

_print('<!-- Tracking file: 02 FEB 21, 10:30 //-->');

?>
