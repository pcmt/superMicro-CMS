<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 14 Jan 2021 */

// Declare variables ($feedback and $value used only when testing)
$setupstatus = $response = $response1 = $response2 = $response3 = $setupstatus = $update = $problem = $invalid_email = $fileError = $submitted_language = $correct_value = $track_me = $posted = "";

$thisAdmin = 'setup'; // For nav

require('./top.php'); // Loads functions.php
require('./language.php');

/*
(1) TRACK_HITS is defined on setup as TRUE or FALSE
(2) If TRUE, check correct value then set cookie to
	either "yes", "no", or delete the cookie
(3) If FALSE, 'track_me' cookie should never be set
	so if cookie is set, page load deletes it
(4) IF TRACK_HITS is not yet defined, do nothing
*/

// Temporary $_values for get 'Current setup' after updating

if (isset($_POST['home_link'])) { $_home_link = trim($_POST['home_link']); }
if (isset($_POST['name'])) { $_name = trim($_POST['name']); }
if (isset($_POST['email'])) { $_email = trim($_POST['email']); }
if (isset($_POST['site_name'])) { $_site_name = trim($_POST['site_name']); }
if (isset($_POST['own_name'])) { $_own_name = trim($_POST['own_name']); }
if (isset($_POST['contact_text'])) { $_contact_text = trim(stripslashes($_POST['contact_text'])); }
if (isset($_POST['contact_menu'])) {
	$_contact_menu = trim(stripslashes($_POST['contact_menu']));
	$_contact_menu = allowedChars($_contact_menu);
}

// YES / NO constants
if (isset($_POST['menu'])) { $_menu = trim($_POST['menu']); }
if (isset($_POST['debug'])) { $_debug = trim($_POST['debug']); }
if (isset($_POST['track'])) { $_track = trim($_POST['track']); }
if (isset($_POST['suffix_it'])) { $_suffix_it = trim($_POST['suffix_it']); }

// For 'Track my hits' track cookie
if (defined('TRACK_HITS')) {

	if (TRACK_HITS) {

		// Set or delete cookie
		if (isset($_POST['submit1'])) {

			$correct_value = FALSE;
			// First check for a correct value
			// otherwise do nothing ($problem = TRUE)
			$posted = trim($_POST['track_me']);
			$correct = array('YES','NO', '');
			foreach ($correct as $check) {
				if ($check == $posted) {
					$correct_value = TRUE;
				}
			}

			// Cookie action (does not affect box in form)
			if ($correct_value) { // Proceed with cookie
				if ($posted == 'YES') { // Track
					setcookie("track", "yes", time() + 31556926, "/"); // One year
				} elseif ($posted == 'NO') { // Don't track, so set track cookie
					setcookie("track", "no", time() + 31556926, "/"); // One year
				} elseif ($posted == '') { // Don't track, so unset track cookie
					if (isset($_COOKIE["track"])) {
						setcookie("track", "", time() - 3600, "/");
					}
				}
			} else {
				$problem = TRUE;
			}

		} // End of 'if submit'

	} else { // Do this on page load

		if (isset($_COOKIE["track"])) {
			setcookie("track", "", time() - 3600, "/");
		}

	} // End of 'if TRACK HITS'

} // End of 'if defined'

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php

if (function_exists('p_title')) {
	p_title('setup');
} else {
	_print('Install the latest version of functions.php');
}

?></title>
<?php if (file_exists('../inc/settings.php')) { ?>
<link rel="shortcut icon" href="<?php _print(LOCATION); ?>favicon.ico">
<?php } ?>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="stylesheet.css" type="text/css">

</head>
<body>

<?php

/* -------------------------------------------------- */
// Start login

if (!$login) {
// Logged out

	if (!file_exists('./login-form.php')) {
		_print("Error. The file '/admin/login-form.php' does not exist. It must be installed.");
		exit();
	} else {
		require('./login-form.php');
	}

} elseif ($login) {

	/* -------------------------------------------------- */
	// Logged in

	// Declare the version
	$version = '3.11'; // Edit footer.php and text/index.txt as well

/* ================================================== */
/* SECTION 1: PREPARATORY */
/* ================================================== */

	$do_setup = TRUE; // Falsified if check fails

	// Set up vars for settings.php
	// -> $site_location | $admin for settings.php
	// NOTE: nothing is backslashed in WIN because paths are either
	// 'basenamed' (unslashed) or from (forwardslashed) $_SERVER

	/* -------------------------------------------------- */
	// Verify 'admin' folder name, otherwise don't setup

	$admin = basename(dirname(__FILE__));
	if (empty($admin)) {
		$admin = basename(getcwd());
	}
	// (Strip all slashes just in case)
	$admin = str_replace('/', '', $admin);
	// Test name
	if (!preg_match('/[A-Za-z]/', $admin)) {
		$do_setup = FALSE;
		$response1 = "<em>Problem: the 'admin' folder name must consist only of alphabetical characters. Rename <b>{$admin}</b>.</em>";
	}

	/* -------------------------------------------------- */
	// Get server vars

	$httpHost = $_SERVER['HTTP_HOST']; // Domain
	$serverName = $_SERVER['SERVER_NAME']; // Domain
	$serverSoftware = $_SERVER['SERVER_SOFTWARE']; // Apache etc
	$serverScriptName = $_SERVER['SCRIPT_NAME']; // URL path to current script
	// Fallback
	if (!isset($serverScriptName) || empty($serverScriptName)) {
		$serverScriptName = phpSELF();
	}

	/* -------------------------------------------------- */
	// Get the domain as $domain otherwise don't setup

	if (!empty($httpHost)) {
		$domain = $httpHost;
	} elseif (!empty($serverName)) {
		$domain = $serverName;
	} else {
		$do_setup = FALSE;
		$response2 = '<em>Problem: site domain not detected.</em>';
	}

	/* -------------------------------------------------- */
	// Get $path as /path/ for URLs (after domain name)
	// i.e. / for root install or /subfolder/subfolder/ for subfolder install

	$localpath = $serverScriptName;
	$thisfile = basename(__FILE__);
	$strip = "{$admin}/$thisfile";
	$path = str_replace($strip, "", $localpath);
	// Strip multiple slashes just in case
	$path = preg_replace('#/{2,}#', '/', $path);

	/* -------------------------------------------------- */
	// Get protocol (https or http) otherwise don't setup

	if (function_exists('get_protocol')) { // Function added 21 Nov 18
		$protocol = get_protocol() ? 'https://' : 'http://'; // See functions.php
		// Check it's returned one or the other
		// if (($protocol == 'https://') || ($protocol == 'http://')) {
			// echo 'get_protocol function works'; // For testing only
		// }
	} else { // If function doesn't exist
		if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		}
	}

	if ($protocol == " ") {
		$do_setup = FALSE;
		$response3 = '<em>Problem: https or http not detected.</em>';
	}

	/* -------------------------------------------------- */
	// Get $site_location for settings.php

	$urlpath = $domain . $path;
	// Strip multiple slashes just in case
	$urlpath = preg_replace('#/{2,}#', '/', $urlpath);
	$site_location = $protocol . $urlpath;

	/* -------------------------------------------------- */
	/* TEST FOR APACHE */

	if (!empty($serverSoftware)) {
		$apache_test = strtolower($serverSoftware);
		if (strpos($apache_test, 'apache') !== FALSE) {
			$apache = 'TRUE';
		} else {
			$apache = 'FALSE';
		}
	} elseif (in_array('apache', strtolower($_SERVER))) {
		$apache = 'TRUE';
	} else {
		$apache = 'FALSE';
	}

	/* -------------------------------------------------- */
	/* TEST FOR WINDOWS */

	if ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') || (DIRECTORY_SEPARATOR == '\\')) {
		$windows = 'TRUE';
	} else {
		$windows = 'FALSE';
	}

	/* -------------------------------------------------- */
	/* SERVER SOFTWARE */

	if (empty($serverSoftware)) { // Feedback only (not used anywhere)
		$serverSoftware = 'FALSE';
	}

	/* -------------------------------------------------- */
	/* OPERATING SYSTEM */

	$opSystem = (strtoupper(PHP_OS)); // Feedback only (not used anywhere)

	/* -------------------------------------------------- */
	/* CMS FOLDER DIRECTORY INDEX */
	// Delete any conflicting directory index file preserving index.php only

	foreach(array('index.html', 'index.htm', 'default.html', 'default.htm','Default.html', 'Default.htm', 'iisstart.htm') as $indexfile) {
		$deletefile = "../{$indexfile}";
		if (file_exists($deletefile)) {
			unlink($deletefile);
		}
	}

	/* -------------------------------------------------- */
	/* OTHER BITS */

	if (file_exists('../e.php')) { // Contact page renamed 20 Jan 2020
		$contact_page = TRUE;
	} else {
		$contact_page = FALSE;
	}

	// For navigation links and response box
	if (!file_exists('../inc/settings.php')) {
		$settings = FALSE;
		$setupstatus = '<em><b>Setup does not yet exist.</b></em>';
	} else {
		$settings = TRUE;
		// unset ($setupstatus);
		$setupstatus = NULL;
	}

	/* -------------------------------------------------- */
	// Submit setup
	// Assume there are now no error responses on initial page load checks
	// i.e. 'admin' folder name, domain detection and protocol detection
	// Further errors may result if the form has not been filled in correctly

/* ================================================== */
/* SECTION 2: SETUP SUBMIT */
/* ================================================== */

	if (isset($_POST['submit1']) && $do_setup) {

		/* -------------------------------------------------- */
		/* CHECK THE FORM */

		$home_link = '';
		$home_link = trim($_POST['home_link']);
		$home_link = allowedChars($home_link);
		if (strlen($home_link) < 1) {
			$problem = TRUE;
			$home_link = FALSE;
		}

		$name = '';
		$name = trim($_POST['name']);
		$name = allowedChars($name);
		// $name = escapeSingle($name);
		if (strlen($name) < 1) {
			$problem = TRUE;
			$name = FALSE;
		}

		// Different vars so YES/NO is printed
		$alphabetical = $menu = '';
		$menu = trim($_POST['menu']);
		if (($menu != 'YES') && ($menu != 'NO')) {
			$problem = TRUE;
			$menu = FALSE;
		} elseif ($menu == 'YES') {
			$alphabetical = 'TRUE';
		} elseif ($menu == 'NO') {
			$alphabetical = 'FALSE';
		}

		// Different vars so YES/NO is printed
		$show_errors = $debug = '';
		$debug = trim($_POST['debug']);
		if (($debug != 'YES') && ($debug != 'NO')) {
			$problem = TRUE;
			$debug = FALSE;
		} elseif ($debug == 'YES') {
			$show_errors = 'TRUE';
		} elseif ($debug == 'NO') {
			$show_errors = 'FALSE';
		}

		// Different vars so YES/NO is printed
		$track_hits = $track = '';
		$track = trim($_POST['track']);
		if (($track != 'YES') && ($track != 'NO')) {
			$problem = TRUE;
			$track = FALSE;
		} elseif ($track == 'YES') {
			$track_hits = 'TRUE';
		} elseif ($track == 'NO') {
			$track_hits = 'FALSE';
		}

		// See top of file for 'Track my hits' cookie

		// Different vars so YES/NO is printed
		$php_ext = $suffix_it = '';
		$suffix_it = trim($_POST['suffix_it']);
		if (($suffix_it != 'YES') && ($suffix_it != 'NO')) {
			$problem = TRUE;
			$suffix_it = FALSE;
		} elseif ($suffix_it == 'YES') {
			$php_ext = 'TRUE';
		} elseif ($suffix_it == 'NO') {
			$php_ext = 'FALSE';
		}

		$email = '';
		$email = trim($_POST['email']);
		if (strlen($email) < 1) {
			$problem = TRUE;
			$email = FALSE;
		}
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$problem = TRUE;
			$email = FALSE;
			$invalid_email = TRUE;
		}

		$site_name = '';
		$site_name = trim($_POST['site_name']);
		$site_name = allowedChars($site_name);
		// $site_name = escapeSingle($site_name);
		if (strlen($site_name) < 1) {
			$problem = TRUE;
			$name = FALSE;
		}

		$own_name = '';
		$own_name = trim($_POST['own_name']);
		$own_name = allowedChars($own_name);
		if (strlen($own_name) < 1) {
			$problem = TRUE;
			$own_name = FALSE;
		}

		if ($contact_page) {
			$contact_text = trim($_POST['contact_text']);
			$contact_text = allowedChars($contact_text);
			if (strlen($contact_text) < 1) {
				$problem = TRUE;
				$contact_text = FALSE;
			}

			$contact_menu = trim($_POST['contact_menu']);
			$contact_menu = allowedChars($contact_menu);
		}

		// This value is not visible on the page
		if ((!$_POST['font_type'] == 'google') || (!$_POST['font_type'] == 'hosted')) {
			$problem = TRUE;
			$font_type = FALSE;
		} else {
			$font_type = $_POST['font_type'];
		}

		// This value is not visible on the page
		$lang_attr = $_POST['lang_attr'];
		$langs = array('en', 'fr', 'de', 'es');
		foreach ($langs as $val) {
			if (!$langs == $val) {
				$problem = TRUE;
				$lang_attr = FALSE;
			}
		}

		if ($lang_attr) {
			if ($lang_attr == 'en') {
				$submitted_language = 'English';
			}
			if ($lang_attr == 'fr') {
				$submitted_language = 'French';
			}
			if ($lang_attr == 'de') {
				$submitted_language = 'German';
			}
			if ($lang_attr == 'es') {
				$submitted_language = 'Spanish';
			}
		}

		if ($problem) {
			if ($fileError) {
				$response1 = '<em>Setup cannot proceed until the error is corrected.</em>';
			} else {
				$response1 = '<em>There was a problem with the settings you entered (see below).</em>';
			}
		}

		if (!$problem) {

			/* -------------------------------------------------- */
			/* WRITE SETTINGS */

			// Update message
			if (file_exists('../inc/settings.php')) {
				$update = TRUE;
			}

			// Write/overwrite settings file
			$settings = '../inc/settings.php';
			$settings_text = "<?php

if(!defined('ACCESS')) {
	die('Direct access not permitted to settings.php.');
}

define('LOCATION', '{$site_location}');
define('ADMIN', '{$admin}');
define('APACHE', {$apache});
define('WINDOWS', {$windows});
define('OPSYS', '{$opSystem}');
define('HOME_LINK', '{$home_link}');
define('NAME', '{$name}');
define('ALPHABETICAL', {$alphabetical});
define('SHOW_ERRORS', {$show_errors});
define('TRACK_HITS', {$track_hits});
define('PHP_EXT', {$php_ext});
define('EMAIL', '{$email}');
define('SITE_NAME', '{$site_name}');
define('OWN_NAME', '{$own_name}');
define('CONTACT_TEXT', '{$contact_text}');
define('CONTACT_MENU', '{$contact_menu}');
define('FONT_TYPE', '{$font_type}');
define('LANG_ATTR', '{$lang_attr}');
define('VERSION', '{$version}');

?>";
			$fp2 = @fopen($settings, 'w+');
			fwrite($fp2, $settings_text);
			@fclose($fp2);

			if ($update) {
				$action = 'updated';
			} else {
				$action = 'entered';
			}

			$response1 = "<em>Settings {$action}.</em>";

			// Now can delete install.php
			if (file_exists('./install.php')) {
				unlink('./install.php');
			}

		} // End of no problem with form

	} else { // Form not submitted

		$response1 = "{$setupstatus}<em> No action requested. Example settings shown.</em>";
	}

	// End submit setup
	/* -------------------------------------------------- */
	// Submit reset

	if (isset($_POST['submit2'])) {
		if (file_exists('../inc/settings.php')) {
			$response1 = '<em>Current settings shown. To change the settings, edit as required then <b>Submit setup</b>.</em>';
		} else {
			$response1 = '<em><b>Setup does not yet exist.</b> Example settings shown.</em>';
		}
	}

/* ================================================== */
/* SECTION 3: START PAGE, H1 & NAVIGATION MENU */
/* ================================================== */

?>

<div id="wrap">

<h1><?php

	if (function_exists('h1')) {
		h1('setup');
	} else {
		_print('Install the latest version of functions.php');
	}

?></h1>

<?php

	if (file_exists('./nav.php')) {
		require('./nav.php');
	} else {
		_print("Error. The file '/admin/nav.php' does not exist. It must be installed.");
		exit();
	}

?>

<h3>Settings for superMicro CMS</h3>

<?php

/* ================================================== */
/* SECTION 4: TOP BOX FEEDBACK */
/* ================================================== */

	if (file_exists('./install.php')) {
		_print_nlb('<p class="important">IMPORTANT: the file <a href="./install.php">/' . $admin . '/install.php</a> still exists and should now be deleted. Submit setup to delete it.</p>');
	}

?>

	<div id="response">

<?php

/* ================================================== */
/* SECTION 5: TOP BOX FEEDBACK */
/* ================================================== */

_print('<p><span class="padded-multiline">' . $response1 . ' ' . $response2 . ' ' . $response3 . '</span></p>');

?>

	</div>

<h3>Enter as required</h3>

<?php

/* ================================================== */
/* SECTION 6: INPUT BOXES */
/* ================================================== */

?>

<form action="<?php _print($self); ?>" method="post" accept-charset="UTF-8">

	<div id="boxes2">

		<div class="group one">

<label>Menu text for home page</label>
<input type="text" name="home_link" size="60" value="<?php

	if (isset($_POST['submit2'])) {
		if (isset($_home_link)) {
			_print($_home_link);
		} else {
			_print(HOME_LINK);
		}
	} elseif (isset($_POST['submit1'])) {
		if (!$home_link && $problem) {
			_print('Enter menu text');
		} else {
			_print(stripslashes($home_link));
		}
	} elseif (file_exists('../inc/settings.php') && defined('HOME_LINK')) {
		_print(HOME_LINK);
	} else {
		_print('Home Page');
	}

?>
" maxlength="60">

<label>Name in footer</label>
<input type="text" name="name" size="60" value="<?php

	if (isset($_POST['submit2'])) {
		if (isset($_name)) {
			_print($_name);
		} else {
			_print(NAME);
		}
	} elseif (isset($_POST['submit1'])) {
		if (!$name && $problem) {
			_print('Enter a name');
		} else {
			_print(stripslashes($name));
		}
	} elseif (file_exists('../inc/settings.php') && defined('NAME')) {
		_print(NAME);
	} else {
		_print('Josephine Bloggs');
	}

?>
" maxlength="60">

<label>Alphabetical menu (YES/NO)</label>
<input type="text" name="menu" size="60" value="<?php

	if (isset($_POST['submit2'])) {
		if (isset($_menu)) {
			_print($_menu);
		} else {
			_print(ALPHABETICAL);
		}
	} elseif (isset($_POST['submit1'])) {
		if (!$menu && $problem) {
			_print('Enter YES or NO');
		} else {
			_print($menu);
		}
	} elseif (file_exists('../inc/settings.php') && defined('ALPHABETICAL')) {
		if (ALPHABETICAL) {
			_print('YES');
		} else {
			_print('NO');
		}
	} else {
		_print('YES');
	}

?>
" maxlength="3">

<label>Debug (YES/NO) [ <a href="https://supermicrocms.com/debug" target="_blank">info</a> ]</label>
<input type="text" name="debug" size="60" value="<?php

	if (isset($_POST['submit2'])) {
		if (isset($_debug)) {
			_print($_debug);
		} else {
			_print(SHOW_ERRORS);
		}
	} elseif (isset($_POST['submit1'])) {
		if (!$debug && $problem) {
			_print('Enter YES or NO');
		} else {
			_print($debug);
		}
	} elseif (file_exists('../inc/settings.php') && defined('SHOW_ERRORS')) {
		if (SHOW_ERRORS) {
			_print('YES');
		} else {
			_print('NO');
		}
	} else {
		_print('NO');
	}

?>
" maxlength="3">

<label>Track hits (YES/NO) [ <a href="https://supermicrocms.com/visitor-tracking" target="_blank">info</a> ]</label>
<input type="text" name="track" size="60" value="<?php

	if (isset($_POST['submit2'])) {
		if (isset($_track)) {
			_print($_track);
		} else {
			_print(TRACK_HITS);
		}
	} elseif (isset($_POST['submit1'])) {
		if (!$track && $problem) {
			_print('Enter YES or NO');
		} else {
			_print($track);
		}
	} elseif (file_exists('../inc/settings.php') && defined('TRACK_HITS')) {
		if (TRACK_HITS) {
			_print('YES');
		} else {
			_print('NO');
		}
	} else {
		_print('NO');
	}

?>
" maxlength="3">

		</div><!-- End group one //-->

		<div class="group two">

<label>Email address</label>
<input type="text" name="email" size="60" value="<?php

	$invalid_email = ""; // Required to re-declare variable
	if (isset($_POST['submit2'])) {
		if (isset($_email)) {
			_print($_email);
		} else {
			_print(EMAIL);
		}
	} elseif (isset($_POST['submit1'])) {
		if (!$email && $problem) {
			_print('Invalid email address');
		} else {
			if ($invalid_email) {
				_print(trim($_POST['email']));
			} else {
				_print($email);
			}
		}
	} elseif (file_exists('../inc/settings.php') && defined('EMAIL')) {
		_print(EMAIL);
	} else {
		_print('mail@mydomain.com');
	}

?>
" maxlength="60">

<label>Your name</label>
<input type="text" name="own_name" size="60" value="<?php

	if (isset($_POST['submit2'])) {
		if (isset($_own_name)) {
			_print($_own_name);
		} else {
			_print(OWN_NAME);
		}
	} elseif (isset($_POST['submit1'])) {
		if (!$own_name && $problem) {
			_print('Enter a name');
		} else {
			_print(stripslashes($own_name));
		}
	} elseif (file_exists('../inc/settings.php') && defined('OWN_NAME')) {
		_print(OWN_NAME);
	} else {
		_print('Josephine');
	}

?>
" maxlength="60">

<label>Site name (20 max)</label>
<input type="text" name="site_name" size="20" value="<?php

	if (isset($_POST['submit2'])) {
		if (isset($_site_name)) {
			_print($_site_name);
		} else {
			_print(SITE_NAME);
		}
	} elseif (isset($_POST['submit1'])) {
		if (!$site_name && $problem) {
			_print('Enter site name');
		} else {
			_print(stripslashes($site_name));
		}
	} elseif (file_exists('../inc/settings.php') && defined('SITE_NAME')) {
		_print(SITE_NAME);
	} else {
		_print('My Website');
	}

?>
" maxlength="100">

<label>.php it (YES/NO) [ <a href="https://supermicrocms.com/links" target="_blank">info</a> ]</label>
<input type="text" name="suffix_it" size="60" value="<?php

	if (isset($_POST['submit2'])) {
		if (isset($_suffix_it)) {
			_print($_suffix_it);
		} else {
			_print(PHP_EXT);
		}
	} elseif (isset($_POST['submit1'])) {
		if (!$suffix_it && $problem) {
			_print('Enter YES or NO');
		} else {
			_print($suffix_it);
		}
	} elseif (file_exists('../inc/settings.php') && defined('PHP_EXT')) {
		if (PHP_EXT) {
			_print('YES');
		} else {
			_print('NO');
		}
	} else {
		_print('NO');
	}

?>
" maxlength="3">

<label>Track my hits (YES/NO) [ <a href="https://supermicrocms.com/visitor-tracking#myhits" target="_blank">info</a> ]</label>
<input type="text" name="track_me" size="60" value="<?php

	if (defined('TRACK_HITS')) {
		if (isset($_POST['submit1']) && TRACK_HITS) { // If not TRACK_HITS, do nothing

			if (!$correct_value) {
				_print('Enter YES or NO or leave empty');
			} else {
				if ($track == 'NO') { // Only for when actually posted
					_print('');
				} elseif ($track == 'YES') {
					if ($_POST['track_me'] == 'YES') {
						_print('YES');
					}
					if ($_POST['track_me'] == 'NO') {
						_print('NO');
					}
					if ($_POST['track_me'] == '') {
						_print('');
					}
				} else {
					_print('');
				}
			}

		} elseif (isset($_POST['submit2']) && (TRACK_HITS == FALSE)) {
			_print(''); // Make sure nothing appears in the box
		} else { // Nothing posted

			if (isset($_COOKIE["track"]) && ($_COOKIE["track"] == 'yes')) {
				_print('YES');
			}
			if (isset($_COOKIE["track"]) && ($_COOKIE["track"] == 'no')) {
				_print('NO');
			}
			if (!isset($_COOKIE["track"])) {
				_print('');
			}

		}
	} // Not defined so nothing in this box

?>
" maxlength="3">

		</div><!-- End group two //-->

<?php

	/* -------------------------------------------------- */
	if ($contact_page) { // Display #bottom

?>

		<div id="bottom">

<label>Contact page introductory text</label>
<textarea name="contact_text" size="60" rows="4" cols="30"><?php

		if (isset($_POST['submit2'])) {
			if (isset($_contact_text)) {
				_print($_contact_text);
			} else {
				_print(CONTACT_TEXT);
			}
		} elseif (isset($_POST['submit1'])) {
			if (!$contact_text && $problem) {
				_print('Enter some text');
			} else {
				_print(stripslashes($contact_text));
			}
		} elseif (file_exists('../inc/settings.php') && defined('CONTACT_TEXT')) {
			_print(CONTACT_TEXT);
		} else {
			_print('To get in touch, feel free to use this contact form (all fields required). The form will send me an email. Privacy respected.');
		}

?></textarea>

<label>Menu text for contact page (leave blank to hide contact page from menu)</label>
<input type="text" name="contact_menu" size="60" value="<?php

		if (isset($_POST['submit2'])) {
			if (isset($_contact_menu)) {
				_print($_contact_menu);
			} else {
				_print(CONTACT_MENU);
			}
		} elseif (isset($_POST['submit1'])) {
			_print(stripslashes($contact_menu)); // Whatever was entered in the form
		} elseif (file_exists('../inc/settings.php') && defined('CONTACT_MENU')) {
			_print(CONTACT_MENU); // Blank if defined as ''
		} else {
			_print('Contact'); // Fallback: no settings, nothing submitted
		}

?>
" maxlength="100">

<?php

		_print_nlb("</div><!-- end bottom //-->\n");

	} else { // No contact page

		_print('
<div id="bottom">

<p>The optional contact page (e.php) is not installed.</p>

</div>
');

	}

?>

	</div><!-- End boxes 2 //-->

<?php

/* ================================================== */
/* SECTION 7: BUTTONS ETC */
/* ================================================== */

?>

	<div id="buttons2">

<div>
<select id="dropdown" name="font_type"><?php

$hosted_selected = '
<option value="hosted">Hosted fonts</option>
<option value="google">Google fonts</option>
';

$google_selected = '
<option value="google">Google fonts</option>
<option value="hosted">Hosted fonts</option>
';

$nothing_selected = '
<option>Select</option>
<option value="google">Google fonts</option>
<option value="hosted">Hosted fonts</option>
';

// If posted
if (isset($_POST['submit1'])) {

	if ($font_type == 'google') { // If google
		_print($google_selected); // Show google top
	} elseif ($font_type == 'hosted') { // hosted
		_print($hosted_selected); // Show hosted top
	} else {
		_print($nothing_selected);
	}

// else not posted
} elseif (defined('FONT_TYPE') && (FONT_TYPE == 'google')) { // From settings
	_print($google_selected); // Show google top
} else { // Starting position: nothing posted, nothing defined
	_print($hosted_selected); // Show hosted top
}

?>
</select> <label>Font type [ <a href="https://supermicrocms.com/font-styles" target="_blank">info</a> ]</label>
</div>

<select id="dropdown" name="lang_attr"><?php

	if (defined('LANG_ATTR')) {
		if (LANG_ATTR == 'en') {
			$language = 'English';
		}
		if (LANG_ATTR == 'fr') {
			$language = 'French';
		}
		if (LANG_ATTR == 'de') {
			$language = 'German';
		}
		if (LANG_ATTR == 'es') {
			$language = 'Spanish';
		}
	}

	/* -------------------------------------------------- */
	/* SELECT LANG */
	// For <html lang="??">

	// Selected option
	if (isset($_POST['submit1'])) {
		_print_nlab('<option value="' . $_POST['lang_attr'] . '">' . $submitted_language . '</option>');
	} elseif (defined('LANG_ATTR')) {
		_print_nlab('<option value="' . LANG_ATTR . '">' . $language . '</option>');
	}

?>
<option value="en">English</option>
<option value="fr">French</option>
<option value="de">German</option>
<option value="es">Spanish</option>
</select> <label>Language [ <a href="https://supermicrocms.com/language" target="_blank">info</a> ]</label>

<hr>

<input type="submit" name="submit1" value="Submit setup">
<input type="submit" name="submit2" class="fade" value="Current setup"><?php _print("\n"); ?>

<?php

/* ================================================== */
/* SECTION 8: INFO BELOW BUTTONS */
/* ================================================== */

	_print("<hr>

<p class=\"break_word\">Home page:<strong><br>{$site_location}</strong></p>
<p>Server software: <strong>{$serverSoftware}</strong></p>
<p>Operating system: <strong>{$opSystem}</strong></p>

");

	if ($protocol == 'https://') {
		_print_nlb('<p class="break_word">The server is <strong>https</strong> (secure).</p>');
	} elseif ($protocol == 'http://') {
		_print_nlb('<p class="break_word">The server is <strong>http</strong> (not secure).</p>');
	} else {
		_print_nlb('<p class="break_word">Problem: server protocol (https or http) not detected. Setup can <strong>not</strong> proceed.</p>');
	}

?>
<p>Optional <strong><a href="./stopwords.php">stopwords</a></strong> for contact page and comments.</p>
<p>See also <a href="https://supermicrocms.com/setup" target="_blank">about setup&nbsp;&#187;</a></p>

	</div>

</form><!-- Form contains 'boxes' and 'buttons' divs //-->

<?php

// Uncomment this for tests (add more tests as required)
/*
if (file_exists('./test.php')) {
	include('./test.php');
}
*/

	include('./footer.php');
} else {

/* ================================================== */
/* END 'IF LOGGED IN' */
/* ================================================== */

	_print('<p>Login could not be verified.</p>');
}

?>

</div>

</body>
</html>