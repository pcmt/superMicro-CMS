<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/*

/* Last updated 07 Dec 2020 */

/*

EVERY TIME THE SETUP PAGE IS ACCESSED:

* top.php sets the test_cookie and checks the login cookie

* Declares the version number for /inc/settings.php

* Checks the name of the 'admin' folder for /inc/settings.php
(prevents setup if incorrect)

* Attempts to get the domain of the installation
(prevents setup if it fails)

* Gets the path to the folder of the installation

* Attempts to detect whether http or https

* Uses (3) (4) and (5) to set $site_location in settings.php

* Attempts to detect if server is Apache for settings.php

* Attempts to detect if server is Window for settings.php
Determines whether PHP mail function can be used

* Attempts to detect server software for settings.php

* Attempts to delete any conflicting directory index file

* Checks whether e.php exists to determine whether
the text input box should be displayed in setup

* Checks whether settings.php already exists to notify
the user in the 'action' box at the top of the page

* Displays the input boxes

* Displays language selection dropdown, submit setup button
and some status information

WHEN SETUP IS SUBMITTED:

(1) Checks if setup can proceed

(2) Checks whether the input boxes have been filled in correctly
and notifies the user accordingly

(3) Attempts to write or update settings.php

(4) Deletes install.php if it exists

SEE ALSO install.php which should have already written:

/admin/password.php
/css/stylesheet.css
/css/extra.css
/inc/inmenu.txt
/inc/password.php
/pages/index.txt

and if it already exists updates:

/admin/password.php

The default files for install.php to write are in /admin/text/

If these files can't be written on install the system won't work
and the setup link won't be displayed so there is no need for further
write tests in this file.

The reason is to allow the system to be updated from the download
files without over-writing them. It can then be updated by uploading
everything in the download folders - not the folders themselves.

NOTE: the existence of .htaccess in the root folder does not mean
it does anything. It probably does if the server is Apache but only
if mod_rewrite is enabled.

*/

// Declare variables
$setupstatus = $response = $response1 = $response2 = $response3 = $setupstatus = $update = $problem = $invalid_email = $fileError = NULL;

$thisPage = 'setup';

require('./top.php'); // Loads functions.php
require('./language.php');

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
<link rel="shortcut icon" href="<?php echo LOCATION; ?>favicon.ico">
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

?>
	<div id="loginform">

<h1>superMicro CMS <i>login</i></h1>

<?php

	if ($notice) {
		echo "\n" . $notice . "\n"; // From top.php (cookie test response)
	}

?>

<form id="pw" action="<?php echo $self; ?>" method="post">
<label><b>Enter password:</b></label>
<input type="hidden" name="form" value="login">
<input type="password" name="password" size="25" maxlength="32">
<input type="submit" name="submit0" value="Submit Password">
</form>

<?php

	if ($response) {
		echo '<p><em>' . $response . '</em></p>'; // If the user didn't do something
		echo "\n";
	}

	// Footer link etc
	if (function_exists('loggedoutFooter')) {
		// Prints link to home page if 'dofooter' + lost/forgotten password link if logged out
		loggedoutFooter();
	} else {
		echo "\n";
		echo '<p>Missing function. Install the latest version of <strong>superMicro CMS</strong>.</p>';

	}

	echo "\n";

?>

	</div>

<?php

} elseif ($login) {

	/* -------------------------------------------------- */
	// Logged in

	// Declare the version
	$version = '3.10'; // Edit footer.php and text/index.txt as well

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

		// Added Feb 2020
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

		$font_type = $_POST['font_type']; // No option not to Post

		$lang_attr = $_POST['lang_attr']; // No option not to Post

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
			$fp2 = @fopen($settings, 'w+'); // Changed from 'wb' 30 Nov 18
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

<p id="nav"><?php if ($settings) { ?>
<a href="<?php echo $site_location; ?>">&#171;&nbsp;Site</a> 
<a href="./index.php" title="Create/edit/delete pages">Pages</a> 
<a href="./images.php" title="Upload or delete images">Images</a> 
<a href="./htaccess.php" title="Create .htaccess file">.htaccess</a> 
<a href="./backup.php" title="Backup">Backup</a><?php } ?> 
<span>Setup</span> 
<a href="?status=logout" title="Logout">Logout</a> 
<a href="https://supermicrocms.com/information" title="Help" class="ext" target="_blank">Help&nbsp;&#187;</a></p>

<h3>Settings for superMicro CMS</h3>

<?php

/* ================================================== */
/* SECTION 4: TOP BOX FEEDBACK */
/* ================================================== */

	if (file_exists('./install.php')) {
		echo '<p class="important">IMPORTANT: the file <a href="./install.php">/' . $admin . '/install.php</a> still exists and should now be deleted. Submit setup to delete it.</p>';
		echo "\n";
	}

?>

	<div id="response">

<?php

/* ================================================== */
/* SECTION 5: TOP BOX FEEDBACK */
/* ================================================== */

echo '<p><span class="padded-multiline">' . $response1 . ' ' . $response2 . ' ' . $response3 . '</span></p>';

?>

	</div>

<h3>Enter as required</h3>

<?php

/* ================================================== */
/* SECTION 6: INPUT BOXES */
/* ================================================== */

?>

<form action="<?php echo $self; ?>" method="post" accept-charset="UTF-8">

	<div id="boxes2">

		<div class="group one">

<label>Menu text for home page<?php if (isset($_POST['submit1']) && (!$home_link) && ($problem)) { echo ' [ <strong>incorrect value</strong> ]'; } ?></label>

<input type="text" name="home_link" size="60" value="<?php

	if (isset($_POST['submit1'])) {
		echo stripslashes($home_link);
	} elseif (file_exists('../inc/settings.php') && defined('HOME_LINK')) {
		echo HOME_LINK;
	} else {
		echo 'Home Page';
	}

?>
" maxlength="60">

<label>Name in footer<?php if (isset($_POST['submit1']) && (!$name) && ($problem)) { echo ' [ <strong>incorrect value</strong> ]'; } ?></label>

<input type="text" name="name" size="60" value="<?php

	if (isset($_POST['submit1'])) {
		echo stripslashes($name);
	} elseif (file_exists('../inc/settings.php') && defined('NAME')) {
		echo NAME;
	} else {
		echo 'Josephine Bloggs';
	}

?>
" maxlength="60">

<label>Alphabetical menu (YES/NO)<?php if (isset($_POST['submit1']) && (!$menu) && ($problem)) { echo ' [ <strong>incorrect value</strong> ]'; } ?></label>

<input type="text" name="menu" size="60" value="<?php

	if (isset($_POST['submit1'])) {
		echo $menu;
	} elseif (file_exists('../inc/settings.php') && defined('ALPHABETICAL')) {
		if (ALPHABETICAL) {
			echo 'YES';
		} else {
			echo 'NO';
		}
	} else {
		echo 'YES';
	}

?>
" maxlength="3">

<label>Debug (YES/NO) [ <a href="https://supermicrocms.com/debug" target="_blank">info</a> ]<?php if (isset($_POST['submit1']) && (!$debug) && ($problem)) { echo ' [ <strong>incorrect value</strong> ]'; } ?></label>

<input type="text" name="debug" size="60" value="<?php

	if (isset($_POST['submit1'])) {
		echo $debug;
	} elseif (file_exists('../inc/settings.php') && defined('SHOW_ERRORS')) {
		if (SHOW_ERRORS) {
			echo 'YES';
		} else {
			echo 'NO';
		}
	} else {
		echo 'NO';
	}

?>
" maxlength="3">

<label>Track hits (YES/NO) [ <a href="https://supermicrocms.com/visitor-tracking" target="_blank">info</a> ]<?php if (isset($_POST['submit1']) && (!$track) && ($problem)) { echo ' [ <strong>incorrect value</strong> ]'; } ?></label>

<input type="text" name="track" size="60" value="<?php

	if (isset($_POST['submit1'])) {
		echo $track;
	} elseif (file_exists('../inc/settings.php') && defined('TRACK_HITS')) {
		if (TRACK_HITS) {
			echo 'YES';
		} else {
			echo 'NO';
		}
	} else {
		echo 'NO';
	}

?>
" maxlength="3">

		</div><!-- End group one //-->

		<div class="group two">

<label>Email address<?php if (isset($_POST['submit1']) && (!$email) && ($problem)) { echo ' [ <strong>incorrect value</strong> ]'; } ?></label>

<input type="text" name="email" size="60" value="<?php

	$invalid_email = ""; // Required to re-declare variable
	if (isset($_POST['submit1'])) {
		if ($invalid_email) {
			echo trim($_POST['email']);
		} else {
			echo $email;
		}
	} elseif (file_exists('../inc/settings.php') && defined('EMAIL')) {
		echo EMAIL;
	} else {
		echo 'mail@mydomain.com';
	}

?>
" maxlength="60">

<label>Your name<?php if (isset($_POST['submit1']) && (!$own_name) && ($problem)) { echo ' [ <strong>incorrect value</strong> ]'; } ?></label>

<input type="text" name="own_name" size="60" value="<?php

		if (isset($_POST['submit1'])) {
			echo stripslashes($own_name);
		} elseif (file_exists('../inc/settings.php') && defined('OWN_NAME')) {
			echo OWN_NAME;
		} else {
			echo 'Josephine';
		}

?>
" maxlength="60">

<label>Site name (20 max)<?php if (isset($_POST['submit1']) && (!$site_name) && ($problem)) { echo ' [ <strong>incorrect value</strong> ]'; } ?></label>

<input type="text" name="site_name" size="20" value="<?php

		if (isset($_POST['submit1'])) {
			echo stripslashes($site_name);
		} elseif (file_exists('../inc/settings.php') && defined('SITE_NAME')) {
			echo SITE_NAME;
		} else {
			echo 'My Website';
		}

?>
" maxlength="100">

<label>.php it (YES/NO) [ <a href="https://supermicrocms.com/links" target="_blank">info</a> ]<?php if (isset($_POST['submit1']) && (!$suffix_it) && ($problem)) { echo ' [ <strong>incorrect value</strong> ]'; } ?></label>

<input type="text" name="suffix_it" size="60" value="<?php

	if (isset($_POST['submit1'])) {
		echo $suffix_it;
	} elseif (file_exists('../inc/settings.php') && defined('PHP_EXT')) {
		if (PHP_EXT) {
			echo 'YES';
		} else {
			echo 'NO';
		}
	} else {
		echo 'NO';
	}

?>
" maxlength="3">

<label>Unused</label>

<input type="text" name="unused" size="60" value="<?php

	echo '';

?>
" maxlength="3">

		</div><!-- End group two //-->

<?php

	/* -------------------------------------------------- */
	if ($contact_page) { // Display #bottom

?>

		<div id="bottom">

<label>Contact page introductory text<?php if (isset($_POST['submit1']) && (!$contact_text) && ($problem)) { echo ' [ <strong>incorrect value</strong> ]'; } ?></label>

<textarea name="contact_text" size="60" rows="4" cols="30"><?php

		if (isset($_POST['submit1'])) {
			echo stripslashes($contact_text);
		} elseif (file_exists('../inc/settings.php') && defined('CONTACT_TEXT')) {
			echo CONTACT_TEXT;
		} else {
			echo 'To get in touch, feel free to use the following contact form. All fields required. The form will send me an email. Your privacy will be respected.';
		}

?></textarea>

<label>Menu text for contact page (leave blank to hide contact page from menu)</label>

<input type="text" name="contact_menu" size="60" value="<?php

		if (isset($_POST['submit1'])) {
			echo stripslashes($contact_menu); // Whatever was entered in the form
		} elseif (file_exists('../inc/settings.php') && defined('CONTACT_MENU')) {
			echo CONTACT_MENU; // Blank if defined as ''
		} else {
			echo 'Contact'; // Fallback: no settings, nothing submitted
		}

?>
" maxlength="100">

<?php

		echo "</div><!-- end bottom //-->\n\n";

	} else { // No contact page

		echo "\n\n";
		echo '		<div id="bottom">';
		echo "\n\n<p>The optional contact page (e.php) is not installed.</p>\n\n";
		echo "		</div>\n";

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

// If posted
if (isset($_POST['submit1'])) {

	if ($font_type == 'google') { // If google
		_print($google_selected); // Show google top
	} else { // hosted
		_print($hosted_selected); // Show hosted top
	}

// Else not posted
} elseif (defined('FONT_TYPE') && (FONT_TYPE == 'google')) { // From settings
	_print($google_selected); // Show google top
} else { // Starting position: nothing posted, nothing defined
	_print($hosted_selected); // Show hosted top
}

?>
</select> <label>Font type [ <a href="https://supermicrocms.com/font-styles" target="_blank">info</a> ]</label>
</div>

<select id="dropdown" name="lang_attr"><?php

	/* -------------------------------------------------- */
	/* SELECT LANG */
	// For <html lang="??">

	// Selected option
	if (isset($_POST['submit1'])) {
		echo "\n";
		echo '<option value="' . $_POST['lang_attr'] . '">' . $language . '</option>';
		echo "\n";
	} elseif (defined('LANG_ATTR')) {
		echo "\n";
		echo '<option value="' . LANG_ATTR . '">' . $language . '</option>';
		echo "\n";
	}

?>
<option value="en">English</option>
<option value="fr">French</option>
<option value="de">German</option>
<option value="es">Spanish</option>
</select> <label>Language [ <a href="https://supermicrocms.com/language" target="_blank">info</a> ]</label>

<hr>

<input type="submit" name="submit1" value="Submit setup">
<input type="submit" name="submit2" class="fade" value="Current setup"><?php echo "\n"; ?>

<?php

/* ================================================== */
/* SECTION 8: INFO BELOW BUTTONS */
/* ================================================== */

	echo "\n<hr>\n";
	echo '<p class="break_word">Home page:<strong><br>' . $site_location . '</strong></p>';
	echo "\n";

	echo "\n";
	echo '<p>Server software: <strong>' . $serverSoftware . '</strong></p>';
	echo "\n";

	echo "\n";
	echo '<p>Operating system: <strong>' . $opSystem . '</strong></p>';
	echo "\n";

	if ($protocol == 'https://') {
		echo '<p class="break_word">The server is <strong>https</strong> (secure).</p>';
		echo "\n";
	} elseif ($protocol == 'http://') {
		echo '<p class="break_word">The server is <strong>http</strong> (not secure).</p>';
		echo "\n";
	} else {
		echo '<p class="break_word">Problem: server protocol (https or http) not detected. Setup can <strong>not</strong> proceed.</p>';
		echo "\n";
	}

?>
<p>Optional <strong><a href="./stopwords.php">stopwords</a></strong> for contact page and comments.</p>
<p>See also <a href="https://supermicrocms.com/setup" target="_blank">about setup&nbsp;&#187;</a></p>

	</div>

</form><!-- Form contains 'boxes' and 'buttons' divs //-->

<?php

	include('./footer.php');

} else {

/* ================================================== */
/* END 'IF LOGGED IN' */
/* ================================================== */

	echo '<p>Login could not be verified.</p>';
}

?>

</div>

</body>
</html>