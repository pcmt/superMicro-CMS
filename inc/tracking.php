<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 08 Dec 2020 */

// This file adds formatted hits to 'listhits.txt'
// to be read by /visits/

// No PHP errors detected in testing so
// normally leave error reporting off
error_reporting(0);
ini_set('display_errors', 0);

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

if(!defined('ACCESS')) {
	die('Direct access not permitted to tracking.php.');
}

// Declare variables
$blocked = $url = $escaped_url = "";

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

// Don't record anything when logged in
if (isset($admin) && !$admin) { /* From top.php */ ?>

<!-- Global site tag (gtag.js) - Google Analytics -->


<?php

	// Date
	$the_date = date('l jS F Y H:i:s');

	// User agent
	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		// Ignore some user agent strings (add manually):
		$ignore = array('crawl', 'spider', 'bot', 'slurp', 'archiver', 'indexer', 'python-requests', 'go-http', 'scrapy');
		foreach ($ignore as $val) {
			if (stripos($user_agent, $val) !== FALSE) {
				$blocked = TRUE;
			}
		}

		// Try to detect device
		if (stristr($user_agent, 'iPhone')) {
			$user_agent = 'iPhone';
		} elseif (stristr($user_agent, 'iPad')) {
			$user_agent = 'iPad';
		} elseif (stristr($user_agent, 'Android')) {
			$user_agent = 'Android';
		} elseif (stristr($user_agent, 'Windows')) {
			$user_agent = 'Windows';
		} elseif (stristr($user_agent, 'Macintosh')) {
			$user_agent = 'Macintosh';
		} elseif (stristr($user_agent, 'Mobi')) {
			$user_agent = 'Mobile';
		} else {
			$user_agent = 'other';
		}

	} else {
		$user_agent = 'no user agent';
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

	/* ============================================================ */
	// Process the hit

	if (!$blocked) {

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
		$hitinfo = 'Page: <a href="' . $escaped_url . '" target="_blank">' . $escaped_url . '</a><br>' . $the_date . '<br>IP address: <a href="https://ip-address.us/lookup/' . $ip . '" target="_blank">' . $ip . '</a> User info: ' . $user_agent . '<br>Referrer: <span>' . $referer . '</span><br><br>';

		// Text file where hits listed
		$hitsfile = './visits/listhits.txt';

		// Permanent text file where hits counted
		$countfile = './visits/count.txt';

		// Temporary text file where hits counted
		$temp_countfile = './visits/tempcount.txt';

		// Nothing happens if no hits file
		if (file_exists($hitsfile)) {

			// Update total count
			// Nothing in this script resets the total count
			if (file_exists($countfile)) {

				$fp1 = @fopen($countfile, 'c+');
				flock($fp1, LOCK_EX);

				$count = (int)fread($fp1, filesize($countfile));
				ftruncate($fp1, 0);
				fseek($fp1, 0);
				fwrite($fp1, $count + 1);

				flock($fp1, LOCK_UN);
				fclose($fp1);

			}

			// Update temporary count
			// If more than 1000, reset file to 0 and delete all hit info
			// otherwise record hit
			if (file_exists($temp_countfile)) {

				$fp2 = @fopen($temp_countfile, 'c+');
				flock($fp2, LOCK_EX);

				$temp_count = (int)fread($fp2, filesize($temp_countfile));
				ftruncate($fp2, 0);
				fseek($fp2, 0);
				fwrite($fp2, $temp_count + 1);

				flock($fp2, LOCK_UN);
				fclose($fp2);

				// Prevent huge hits file being written
				if ($temp_count < 1001) { // If less than 1000 hits

					// Add hit details to top of listhits.txt
					$file_data = $hitinfo . "\n"; // New hit
					$file_data .= file_get_contents($hitsfile); // Add previous hits
					file_put_contents($hitsfile, $file_data); // Update list

				} else { // More than 1001 hits

					// Delete all hits and start again
					$fp4 = @fopen($hitsfile, "w");
					fwrite($fp4, "");
					fclose($fp4);

					// Do the same with the temporary count file
					$fp5 = @fopen($temp_countfile, "w");
					fwrite($fp5, "0");
					fclose($fp5);

				} // End of 'if less than 1000 hits'

			} // End of 'if tempcount file exists'

		} // End of 'if hitsfile exists'

	} // End of 'if not blocked'

	echo "\n<!-- Logged out (hit logged) //-->";

} else { // End of 'if logged out'

	echo "\n<!-- Logged in (hit not logged) //-->";

}

if ($ip) {
	echo "\n<!-- {$ip} //-->";
} else {
	echo "\n<!-- No ip //-->";
}

echo "\n<!-- Tracking {$escaped_url} (tracking file: 10:10 08 DEC 20) //-->\n";

?>
