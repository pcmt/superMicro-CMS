<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 08 Sept 2024 */

define('ACCESS', TRUE);

// Declare variables ($feedback and $value used only when testing)
$setupstatus = $response = $response1 = $response2 = $response3 = $update = $problem = $invalid_email = $posted = $invalid_HEX = $g_zip = $submitted_language = "";

$timestamp = 0; // Numeric

$thisAdmin = 'setup'; // For nav

include('./top.php'); // Loads functions.php
include('./language.php'); // New Aug 24

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php p_title('setup'); ?></title>
<?php includeFileIfExists('./icons.php'); ?>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="styles.css" type="text/css">

</head>
<body>

<?php

/* -------------------------------------------------- */
// Start login

if (!$login) {
// Logged out

	includeFileIfExists('./login-form.php');

} elseif ($login) {

	/* -------------------------------------------------- */
	// Logged in

	// Declare the version
	$version = '6.1'; // Edit /text/index.txt as well

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

	$admin = basename(realpath(dirname(__FILE__)));
	if (empty($admin)) { // Unlikely
		$admin = basename(realpath(getcwd()));
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

	// See top.php (04/09/24)

	/* -------------------------------------------------- */
	// Generate a URL that accurately reflects the location of the website
	// i.e. $site_location
	// If $admin or $serverScriptName are not perfectly aligned with the
	// actual directory structure, the generated paths might be incorrect
	// so ensure these variables are set correctly.

	// Get $path as /path/ for URLs (after domain name)
	// i.e. / for root install or /subfolder/subfolder/ for subfolder install

	$localpath = $serverScriptName;
	$thisfile = basename(__FILE__);
	$strip = "{$admin}/$thisfile";
	$path = str_replace($strip, "", $localpath);
	// Strip multiple slashes just in case
	$path = preg_replace('#/{2,}#', '/', $path);

	// ChatGPT: Overall, you've built a strong, reliable method for obtaining
	// the URL path of the current script.

	/* -------------------------------------------------- */
	// Get protocol (https or http) otherwise don't setup

	// See top.php (04/09/24)

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
	/* OPERATING SYSTEM */

	$opSystem = (strtoupper(PHP_OS));

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
	/* TEST FOR GZIP */

	if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') || substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip')){
		$g_zip = 'TRUE';
	} else {
		$g_zip = 'FALSE';
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
		$setupstatus = '<em><b>Settings not found. Submit setup?</b></em>';
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

		$home_link = ''; // To DEFINE
		$home_link = trim(strip_tags($_POST['home_link']));

		if ((strlen($home_link) < 1) || ($home_link === 'Enter menu text')) {
			$problem = TRUE;
			$home_link = FALSE;
		} else {
			$home_link = allowedChars($home_link);
		}

		/* -------------------------------------------------- */

		$name = ''; // To DEFINE
		$name = trim(strip_tags($_POST['name']));

		if ((strlen($name) < 1) || ($name === 'Enter a name')) {
			$problem = TRUE;
			$name = FALSE;
		} else {
			$name = allowedChars($name);
		}

		/* -------------------------------------------------- */

		$own_name = ''; // To DEFINE
		$own_name = trim(strip_tags($_POST['own_name']));

		if ((strlen($own_name) < 1) || ($own_name === 'Enter your name')) {
			$problem = TRUE;
			$own_name = FALSE;
		} else {
			$own_name = allowedChars($own_name);
		}

		/* -------------------------------------------------- */

		$site_name = ''; // To DEFINE
		$site_name = trim(strip_tags($_POST['site_name']));

		if ((strlen($site_name) < 1) || ($site_name === 'Enter site name')) {
			$problem = TRUE;
			$site_name = FALSE;
		} else {
			$site_name = allowedChars($site_name);
		}

		/* -------------------------------------------------- */
		// YES/NO boxes
		// There are 2 variables:
		// $debug is the value of $_POST['debug']
		// $show_errors is used in define
		// Different vars so YES/NO is printed

		// Null coalescing operator (ensures $debug contains a whitespace-free string
		// from POST data, or an empty string if the debug parameter is not provided)

		$show_errors = ''; // To DEFINE
		$debug = trim($_POST['debug'] ?? '');

		if ($debug === 'YES') {
			$show_errors = 'TRUE';
		} elseif ($debug === 'NO') {
			$show_errors = 'FALSE';
		} else {
			$problem = TRUE;
			$debug = FALSE;
		}

		/* -------------------------------------------------- */

		$alphabetical = ''; // To DEFINE
		$menu = trim($_POST['menu']);

		if ($menu === 'YES') {
			$alphabetical = 'TRUE';
		} elseif ($menu === 'NO') {
			$alphabetical = 'FALSE';
		} else {
			$problem = TRUE;
			$menu = FALSE;
		}

		/* -------------------------------------------------- */

		$show_history = ''; // To DEFINE
		$history = trim($_POST['history']);

		if ($history === 'YES') {
			$show_history = 'TRUE';
		} elseif ($history === 'NO') {
			$show_history = 'FALSE';
		} else {
			$problem = TRUE;
			$history = FALSE;
		}

		/* -------------------------------------------------- */

		$php_ext = ''; // To DEFINE
		$suffix_it = trim($_POST['suffix_it']);

		if ($suffix_it === 'YES') {
			$php_ext = 'TRUE';
		} elseif ($suffix_it === 'NO') {
			$php_ext = 'FALSE';
		} else {
			$problem = TRUE;
			$suffix_it = FALSE;
		}

		/* -------------------------------------------------- */

		$change_footer = ''; // To DEFINE
		$footer_option = trim($_POST['footer_option']);

		if ($footer_option === 'YES') {
			$change_footer = 'TRUE';
		} elseif ($footer_option === 'NO') {
			$change_footer = 'FALSE';
		} else {
			$problem = TRUE;
			$footer_option = FALSE;
		}

		/* -------------------------------------------------- */

		$link_colour = trim($_POST['link_colour']);
		if ((strlen($link_colour) < 1) || ($link_colour === 'Enter a HEX')) {
			$problem = TRUE;
			$link_colour = FALSE;
		}

		if (!preg_match('/^#[a-f0-9]{6}$/i', trim($link_colour))) {
			$problem = TRUE;
			$link_colour = FALSE;
			$invalid_HEX = TRUE;
		}

		/* -------------------------------------------------- */

		$email = trim(strip_tags($_POST['email']));
		if ((strlen($email) < 1) || ($email === 'Invalid email address')) {
			$problem = TRUE;
			$email = FALSE;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$problem = TRUE;
			$email = FALSE;
			$invalid_email = TRUE;
		}

		/* -------------------------------------------------- */

		if ($contact_page) {
			$contact_text = trim($_POST['contact_text']);
			$contact_text = allowedChars($contact_text);
			if ((strlen($contact_text) < 1) || ($contact_text == 'Enter some text')) {
				$problem = TRUE;
				$contact_text = FALSE;
			}

			$contact_menu = trim($_POST['contact_menu']);
			$contact_menu = allowedChars($contact_menu);
			if (!$contact_text) {
				$contact_menu = 'FALSE';
			}
		} else {
			$contact_text = FALSE;
			$contact_menu = '';
		}

		/* -------------------------------------------------- */

		$fonts = substr(trim($_POST['font_type']), 0, 6); // First 6 chars
		if ((!$fonts == 'google') || (!$fonts == 'hosted')) {
			$problem = TRUE;
			$font_type = FALSE;
		} else {
			$font_type = $fonts;
		}

		/* -------------------------------------------------- */

		$lang_attr = substr(trim($_POST['lang_attr']), 0, 2); // First 2 chars
		if ($lang_attr) {
			if ($lang_attr == 'en') {
				$submitted_language = 'English';
			} elseif ($lang_attr == 'fr') {
				$submitted_language = 'French';
			} elseif ($lang_attr == 'de') {
				$submitted_language = 'German';
			} elseif ($lang_attr == 'es') {
				$submitted_language = 'Spanish';
			} else { // Belt and braces
				$problem = TRUE;
				$lang_attr = FALSE;
			}
		}

		/* -------------------------------------------------- */

		if ($problem) {
			$response1 = '<em>There was a problem with the settings you entered.</em>';
		} else {
			/* -------------------------------------------------- */
			/* WRITE SETTINGS */

			// Update message
			if (file_exists('../inc/settings.php')) {
				$update = TRUE;
			}

			$timestamp = time();

			// Write/overwrite settings file
			$settings = '../inc/settings.php';
			$settings_text = "<?php

if(!defined('ACCESS')) {
	die('Direct access not permitted to settings.php');
}

define('LOCATION', '{$site_location}');
define('ADMIN', '{$admin}');
define('APACHE', {$apache}); // TRUE or FALSE
define('WINDOWS', {$windows}); // TRUE or FALSE
define('OPSYS', '{$opSystem}');
define('G_ZIP', {$g_zip}); // TRUE or FALSE
define('HOME_LINK', '{$home_link}');
define('NAME', '{$name}');
define('ALPHABETICAL', {$alphabetical}); // TRUE or FALSE
define('SHOW_ERRORS', {$show_errors}); // TRUE or FALSE
define('PHP_EXT', {$php_ext}); // TRUE or FALSE
define('SHOW_HISTORY', {$show_history}); // TRUE or FALSE
define('FOOTER_OPTION', {$change_footer}); // TRUE or FALSE
define('EMAIL', '{$email}');
define('SITE_NAME', '{$site_name}');
define('SITE_ID', '{$siteID}');
define('OWN_NAME', '{$own_name}');
define('LINK_COLOUR', '{$link_colour}');
define('CONTACT_TEXT', '{$contact_text}');
define('CONTACT_MENU', '{$contact_menu}');
define('FONT_TYPE', '{$font_type}');
define('LANG_ATTR', '{$lang_attr}');
define('VERSION', '{$version}');
define('TIMESTAMP', '{$timestamp}');

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

			// Now delete install.php
			if (file_exists('./install.php')) {
				unlink('./install.php');
			}

			// Now delete siteid.txt
			if (file_exists('./siteid.txt')) {
				unlink('./siteid.txt');
			}

		} // End of no problem with form

	} else { // Form not submitted

		$response1 = "{$setupstatus} <em>No action requested. Example settings shown.</em>";
	}

	// End submit setup
	/* -------------------------------------------------- */
	// Submit reset

	if (isset($_POST['submit2'])) {
		if (file_exists('../inc/settings.php')) {
			$response1 = '<em>Current settings shown. To change the settings, edit as required then <b>Submit setup</b>.</em>';
		} else {
			$response1 = "{$setupstatus} <em>Example settings shown.</em>";
		}
	}

/* ================================================== */
/* SECTION 3: START PAGE, H1 & NAVIGATION MENU */
/* ================================================== */

?>

<div id="o"><div id="wrap">

<h1><?php h1('setup'); ?></h1>

<?php

	includeFileIfExists('./nav.php');

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

	<div id="boxes-setup">

		<div class="group one">

<!-- MENU TEXT ============================== //-->
<label>Home page menu text</label>
<input type="text" name="home_link" size="60" <?php if ($problem && !$home_link) { _print('style="color: red;" '); } ?>value="<?php

	if (isset($_POST['submit1'])) {

		if (!$home_link && $problem) {
			_print('Enter menu text');
		} else {
			_print(stripslashes($home_link));
		}

	} elseif (defined('HOME_LINK')) {
		_print(HOME_LINK);
	} else {
		_print('Home Page');
	}

?>" maxlength="60">

<!-- NAME IN FOOTER ============================== //-->
<label>Name in footer</label>
<input type="text" name="name" size="60" <?php if ($problem && !$name) { _print('style="color: red;" '); } ?>value="<?php

	if (isset($_POST['submit1'])) {

		if (!$name && $problem) {
			_print('Enter a name');
		} else {
			_print(stripslashes($name));
		}

	} elseif (defined('NAME')) {
		_print(NAME);
	} else {
		_print('Josephine Bloggs');
	}

?>" maxlength="60">

<!-- ALPHABETICAL MENU ============================== //-->
<label>Alphabetical menu (YES/NO)</label>
<input type="text" name="menu" size="60" <?php if ($problem && !$menu) { _print('style="color: red;" '); } ?>value="<?php

	if (isset($_POST['submit1'])) {

		if (!$menu && $problem) {
			_print('Enter YES or NO');
		} else {
			_print($menu);
		}

	} elseif (defined('ALPHABETICAL')) {

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

<!-- DEBUG ============================== //-->
<label>Debug (YES/NO) [ <a href="https://web.patricktaylor.com/cms-debug" target="_blank">info</a> ]</label>
<input type="text" name="debug" size="60" <?php if ($problem && !$debug) { _print('style="color: red;" '); } ?>value="<?php

	if (isset($_POST['submit1'])) {

		if (!$debug && $problem) {
			_print('Enter YES or NO');
		} else {
			_print($debug);
		}

	} elseif (defined('SHOW_ERRORS')) {

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

<!-- HISTORY ============================== //-->
<label>Show recent visits (YES/NO)</label>
<input type="text" name="history" size="60" <?php if ($problem && !$history) { _print('style="color: red;" '); } ?>value="<?php

	if (isset($_POST['submit1'])) {

		if (!$history && $problem) {
			_print('Enter YES or NO');
		} else {
			_print($history);
		}

	} elseif (defined('SHOW_HISTORY')) {

		if (SHOW_HISTORY) {
			_print('YES');
		} else {
			_print('NO');
		}

	} else {
		_print('NO');
	}

?>
" maxlength="3">

<!-- LINK COLOUR ============================== //-->
<label>Link colour [ <a href="https://qwwwik.com/links#hex" target="_blank">info</a> ]</label>
<input type="text" name="link_colour" size="60" <?php if ($problem && !$link_colour) { _print('style="color: red;" '); } ?>value="<?php

	$invalid_HEX = ""; // Required to re-declare variable
	if (isset($_POST['submit1'])) {

		if (!$link_colour && $problem) {
			_print('Enter a HEX colour, eg: #ff0099');
		} else {
			if ($invalid_HEX) {
				_print(trim($_POST['link_colour']));
			} else {
				_print($link_colour);
			}
		}

	} elseif (defined('LINK_COLOUR')) {
		_print(LINK_COLOUR);
	} else {
		_print('#FF0099');
	}

?>" maxlength="7">

		</div><!-- End group one //-->

		<div class="group two">

<!-- EMAIL ADDRESS ============================== //-->
<label>Email address</label>
<input type="text" name="email" size="60" <?php if ($problem && !$email) { _print('style="color: red;" '); } ?>value="<?php

	$invalid_email = ""; // Required to re-declare variable
	if (isset($_POST['submit1'])) {

		if (!$email && $problem) {
			_print('Invalid email address');
		} else {
			if ($invalid_email) {
				_print(trim($_POST['email']));
			} else {
				_print($email);
			}
		}

	} elseif (defined('EMAIL')) {
		_print(EMAIL);
	} else {
		_print('mail@mydomain.com');
	}

?>" maxlength="100">

<!-- OWN NAME ============================== //-->
<label>Your name</label>
<input type="text" name="own_name" size="60" <?php if ($problem && !$own_name) { _print('style="color: red;" '); } ?>value="<?php

	if (isset($_POST['submit1'])) {

		if (!$own_name && $problem) {
			_print('Enter your name');
		} else {
			_print(stripslashes($own_name));
		}

	} elseif (defined('OWN_NAME')) {
		_print(OWN_NAME);
	} else {
		_print('Josephine Bloggs');
	}

?>" maxlength="60">

<!-- SITE NAME ============================== //-->
<label>Site name (30 max)</label>
<input type="text" name="site_name" size="60" <?php if ($problem && !$site_name) { _print('style="color: red;" '); } ?>value="<?php

	if (isset($_POST['submit1'])) {

		if (!$site_name && $problem) {
			_print('Enter site name');
		} else {
			_print(stripslashes($site_name));
		}

	} elseif (defined('SITE_NAME')) {
		_print(SITE_NAME);
	} else {
		_print('My Website');
	}

?>" maxlength="30">

<!-- PHP SUFFIX ============================== //-->
<label>.php it (YES/NO) [ <a href="https://web.patricktaylor.com/cms-links" target="_blank">info</a> ]</label>
<input type="text" name="suffix_it" size="60" <?php if ($problem && !$suffix_it) { _print('style="color: red;" '); } ?>value="<?php

	if (isset($_POST['submit1'])) {
		if (!$suffix_it && $problem) {
			_print('Enter YES or NO');
		} else {
			_print($suffix_it);
		}
	} elseif (defined('PHP_EXT')) {
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

<!-- FOOTER ============================== //-->
<label>Light footer (YES/NO)</label>
<input type="text" name="footer_option" size="60" <?php if ($problem && !$footer_option) { _print('style="color: red;" '); } ?>value="<?php

	if (isset($_POST['submit1'])) {

		if (!$footer_option && $problem) {
			_print('Enter YES or NO');
		} else {
			_print($footer_option);
		}

	} elseif (defined('FOOTER_OPTION')) {

		if (FOOTER_OPTION) {
			_print('YES');
		} else {
			_print('NO');
		}

	} else {
		_print('NO');
	}

?>
" maxlength="3">

<!-- NOT USED ============================== //-->
<label>Not used</label>
<input type="text" name="#" size="60" value="<?php

_print('Empty');

?>
" maxlength="3">

		</div><!-- End group two //-->

<?php

	/* -------------------------------------------------- */
	if ($contact_page) { // Display #bottom

?>

		<div id="bottom">

<hr>

<label>Contact page text (delete <em>/e.php</em> if not required).</label>
<div class="textarea-container"><textarea name="contact_text" class="flexitem" rows="4"><?php

	if (isset($_POST['submit2'])) {
		if (isset($_contact_text)) {
			_print(stripslashes($_contact_text));
		} else {
			_print(CONTACT_TEXT);
		}
	} elseif (isset($_POST['submit1'])) {
			if (!$contact_text && $problem) {
				_print('Enter some text');
			} else {
				_print(stripslashes($contact_text));
			}
		} elseif (defined('CONTACT_TEXT')) {
			_print(CONTACT_TEXT);
		} else {
			_print('To get in touch, feel free to use this contact form (all fields required). The form will send me an email. Privacy respected.');
		}

?></textarea></div>

<label>Menu text for contact page (leave blank to hide contact page)</label>
<input type="text" name="contact_menu" size="60" value="<?php

		if (isset($_POST['submit2'])) {
			if (isset($_contact_menu)) {
				_print($_contact_menu);
			} else {
				_print(CONTACT_MENU);
			}
		} elseif (isset($_POST['submit1'])) {
			_print(stripslashes($contact_menu)); // Whatever was entered in the form
		} elseif (defined('CONTACT_MENU')) {
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

	</div><!-- End boxes-setup //-->

<?php

/* ================================================== */
/* SECTION 7: BUTTONS ETC */
/* ================================================== */

?>

	<div id="buttons-setup">

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
</select> <label>Font type [ <a href="https://web.patricktaylor.com/cms-font-styles" target="_blank">info</a> ]</label>
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
</select> <label>Language [ <a href="https://web.patricktaylor.com/language" target="_blank">info</a> ]</label>

<input type="submit" name="submit1" value="Submit setup">
<input type="submit" name="submit2" class="fade" value="Current setup"><?php _print("\n"); ?>

<?php

/* ================================================== */
/* SECTION 8: INFO BELOW BUTTONS */
/* ================================================== */

	_print("<p class=\"break_word\">Home page:<strong><br>{$site_location}</strong></p>
<p>Server software: <strong>{$serverSoftware}</strong></p>
<p>Operating system: <strong>{$opSystem}</strong></p>
<p>Site ID: <strong>{$siteID}</strong></p>

");

	if ($protocol == 'https://') {
		_print_nlb('<p class="break_word">The server is <strong>https</strong> (secure).</p>');
	} elseif ($protocol == 'http://') {
		_print_nlb('<p class="break_word">The server is <strong>http</strong> (not secure).</p>');
	} else {
		_print_nlb('<p class="break_word">Problem: server protocol (https or http) not detected. Setup can <strong>not</strong> proceed.</p>');
	}

	$ver = function_exists('phpversion') ? '<strong>' . phpversion() . '</strong>' : 'not detected.';
	_print_nlb('<p>PHP version ' . $ver . '</p>');

	$av = $g_zip ? '<em>available</em>' : 'not available';
	_print_nlb('<p>GZIP is <strong>' . $av . '</strong></p>');

?>
<p>Optional <strong><a href="./stopwords.php">stopwords</a></strong>.</p>
<p>See also <a href="https://web.patricktaylor.com/cms-setup" target="_blank">about setup&nbsp;&#187;</a></p>

	</div>

</form><!-- Form contains 'boxes' and 'buttons' divs //-->

<?php

	includeFileIfExists('./footer.php');

} else {

	/* -------------------------------------------------- */
	// No $login or !$login

	_print('<p>Login could not be verified.</p>');
}

?>
</div></div>

</body>
</html>