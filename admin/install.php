<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

// Last updated 29 August 2024
// Switched to absolute paths

define('ACCESS', TRUE);

// Declare variables
$sh_password = $salt = $domain = $cookie_status = $test_cookie = $missing_password_php = $incorrect_form_problem = $admin_shp = $admin_s = $file_write_problem = $error = $admin_password_problem = $install = $siteID = $to = $d = $info = '';

$v = "6.1 ";

error_reporting(0);
// error_reporting(E_ALL);

/* CHECK THAT THINGS EXIST ========================== */

// This can't be in functions.php because it checks it
function hasFunction($file_path, $to_check) {

	$file_content = file_get_contents($file_path);
	// Check each function
	foreach ($to_check as $function) {
		// Regular expression for 'name(' or 'name  )'
		$pattern = '/\bfunction\s+' . preg_quote($function, '/') . '\s*\(/';
		if (!preg_match($pattern, $file_content)) {
			return false; // Function missing
		}
	}

	return true; // All functions found
}

/* -------------------------------------------------- */
// Absolute path to folder above this folder (/admin -> root)

$upOnetoRoot = dirname(__DIR__, 1);

/* -------------------------------------------------- */
// Absolute path to this folder (/admin)
$here = __DIR__;

/* -------------------------------------------------- */
// Verify the current /inc/functions file
// Get /inc/functions.php as simple string, not as array to loop through
$file_path = $upOnetoRoot . '/inc/functions.php';

// The current /inc/ functions (keep up to date)
$to_check = array('_print', '_print_nla', '_print_nlab', '_print_nlb', '_autop_newline_preservation_helper', 'autop', 'bits_and', 'absolute_it', 'img_path', 'srcset_path', 'video_path', 'suffix_it');

// Check the file
if (!hasFunction($file_path, $to_check)) {
	$incorrect_form_problem = TRUE;
	$error = "Error: can't install. Missing function in '/inc/functions.php'. Install the latest version.";
}

/* -------------------------------------------------- */
// Verify the current /admin/functions file
// Get /inc/functions.php as simple string, not as array to loop through
$file_path = $here . '/functions.php';


// The current /admin/ functions (keep up to date)
$to_check = array('_print', '_print_nla', '_print_nlab', '_print_nlb', 'p_title', 'h1', 'includeFileIfExists', 'phpSELF', 'loggedoutFooter', 'get_protocol', 'sanitizeIt', 'randomString', 'getPostValue', 'allowedChars', 'allChars', 'getBetween', 'removeEmptyLines', 'stripAnchor');

// Check the file
if (!hasFunction($file_path, $to_check)) {
	$incorrect_form_problem = TRUE;
	echo "Error: can't install. Missing function in '/admin/functions.php'. Install the latest version.";
	exit(); // because this file requires functions
}

/* -------------------------------------------------- */
/* Verify the essential files (root, /inc and /admin) */

// The actual files
$required1 = array(/* root */'/.htaccess', '/index.php', '/preview.php', /* /inc/ */'/inc/404.php', '/inc/content.php', '/inc/error-reporting.php', '/inc/extra-content.php', '/inc/extra-body.php', '/inc/extra-head.php', '/inc/filter-email.php', '/inc/footer.php', '/inc/form.php', '/inc/functions.php', '/inc/history.php', '/inc/html.php', '/inc/index.php', '/inc/lang.php', '/inc/login-form.php', '/inc/menu.php', '/inc/ppp.php', '/inc/prelims.php', '/inc/process-data.php', '/inc/show-history.php', '/inc/stylesheets.php', '/inc/top.php', '/inc/tracking.php', '/inc/languages/en.php', '/data/index.php', '/data/index.txt');

foreach ($required1 as $file) {
	$file = $upOnetoRoot . $file;
	if (!file_exists($file)) { // Exit if an actual file is missing
		echo "Error: the file '{$file}' does not exist. It must be installed.";
		exit(); // because all the files are required
	}
}

// The actual files
$required2 = array(/* /admin */'/backup.php', '/comments.php', '/data.php', '/extras.php', '/footer.php', '/functions.php', '/htaccess.php', '/icons.php', '/images.php', '/index.php', '/language.php', '/list.php', '/login-form.php', '/nav.php', '/password-sample.php', '/setup.php', '/stopwords.php', '/top.php', '/upload.php', '/video.php', '/text/extra-css.txt', '/text/index.txt', '/text/inmenu.txt', '/text/mobile.txt', '/text/password.txt', '/text/stylesheet.txt');

foreach ($required2 as $file) {
	$file = $here . $file;
	if (!file_exists($file)) { // Exit if an actual file is missing
		echo "Error: the file '{$file}' does not exist. It must be installed.";
		exit(); // because all the files are required
	}
}

/* -------------------------------------------------- */
// Delete previous login cookie if it exists
if (isset($_COOKIE["supermicro"])) {
	setcookie("supermicro", "", time() - 3600);
}

/* -------------------------------------------------- */
// For footer.php

global $tm_start;
$tm_start = 0;
$tm_start = array_sum(explode(' ', microtime()));

/* -------------------------------------------------- */
// Check the PHP version

if (version_compare(phpversion(), '7.2.0', '<')) {
	$error = 'superMicro CMS needs PHP version 7.2.0 or later. Your server is running PHP version ' . PHP_VERSION . '. Try installing anyway.';
	$old_phpV = TRUE;
} else {
	$old_phpV = FALSE;
}

/* -------------------------------------------------- */
// Get the path to this folder

$admin_folder = '/' . basename(dirname(__FILE__)) . '/';

/* -------------------------------------------------- */
// Attempt to set test cookie, preferably secure (repeated in top.php)

// Try to get the domain
if (!empty($_SERVER['HTTP_HOST'])) {
	$domain = $_SERVER['HTTP_HOST'];
} elseif (!empty($_SERVER['SERVER_NAME'])) {
	$domain = $_SERVER['SERVER_NAME'];
} else {
	$domain = FALSE;
}

// Try to establish whether SSL or not
if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
	$secure = TRUE;
} else {
	$secure = FALSE;
}

if ($domain && $secure) {
	$secure_cookie = TRUE;
} else {
	$secure_cookie = FALSE;
}

if ($secure_cookie) {
	if (isset($_COOKIE["supermicro_test_cookie"])) {
		setcookie("supermicro_test_cookie", "", time() - 3600, "/", "{$domain}", 1, 1); // Delete any existing cookie
	}
	setcookie("supermicro_test_cookie", "installation", time() + 3600, "/", "{$domain}", 1, 1); // One hour
} else {
	if (isset($_COOKIE["supermicro_test_cookie"])) {
		setcookie("supermicro_test_cookie", "", time() - 3600, "/"); // Delete any existing cookie
	}
	setcookie("supermicro_test_cookie", "installation", time() + 3600, "/"); // One hour. Root - is that right?
}

/* -------------------------------------------------- */

// Get functions.php
include('./functions.php');

// Populate the input boxes if installed
// No longer requires 'include' (03 Dec 20)
if (file_exists('./password.php')) {
	$linesArray = array();
	$linesArray = file('./password.php');
	foreach ($linesArray as $line) {
		if (stripos($line, '$sh_password =') !== FALSE) {
			$sh_password = trim(getBetween($line, '$sh_password = "', '";'));
		}
		if (stripos($line, '$salt =') !== FALSE) {
			$salt = trim(getBetween($line, '$salt = "', '";'));
		}
	}
}

/* -------------------------------------------------- */
/* Form submit */

if (isset($_POST['submit1'])) {

	// Attempt to create and write the files
	// and report failures, preventing further progress

	// Create a new site ID and store it
	$siteidFile = fopen("siteid.txt", "w") or die("Unable to write siteid.txt");
	$ID = randomString( 5 );
	fwrite($siteidFile, $ID);
	fclose($siteidFile);

/*
IMPORTANT! THIS FILE SHOULD BE RUN WITH ALL VERSION UPDATES
Writing these files is because they are not in the download
so that when an update is uploaded they are not overwritten
*/

	// $admin_shp = allChars(trim($_POST['admin_shpassword']));
	// $admin_s = allChars(trim($_POST['admin_salt']));

	$admin_shp = trim($_POST['admin_shpassword']);
	$admin_s = trim($_POST['admin_salt']);

	/* -------------------------------------------------- */
	/* (1) Check the values entered in the form */
	// In this order:

	if (strlen(trim($admin_s)) < 1) {
		$incorrect_form_problem = TRUE;
		$error = "You didn't enter a salt for your admin password.";
	}

	if (strlen(trim($admin_shp)) !== 64) {
		$incorrect_form_problem = TRUE;
		$error = "Your [salted and hashed] admin password should be 64 characters.";
	}

	// If the form was correct
	if (!$incorrect_form_problem) {

		/* -------------------------------------------------- */
		/* (2) Attempt to create the writable files */
		// But don't overwrite existing files

		/* -------------------------------------------------- */
		// /pages/index.txt
		$index = $upOnetoRoot . '/pages/index.txt';
		$text = $here . '/text/index.txt'; // Source

		if (!file_exists($index)) { // Proceed only if it doesn't exist
			if (!file_exists($text)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. <b>{$text} doesn't exist</b>.";
			} else if (!copy($text, $index)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$index}.</b>";
			}
		}

		$text = "";

		/* -------------------------------------------------- */
		// /inc/inmenu.txt
		$inmenu = $upOnetoRoot . '/inc/inmenu.txt';
		$text = $here . '/text/inmenu.txt';

		if (!file_exists($inmenu)) { // Proceed only if it doesn't exist
			if (!file_exists($text)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. <b>{$text} doesn't exist</b>.";
			} else if (!copy($text, $inmenu)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$inmenu}.</b>";
			}
		}

		$text = "";

		/* -------------------------------------------------- */
		// /css/stylesheet.css
		$stylesheet = $upOnetoRoot . '/css/stylesheet.css';
		$text = $here . '/text/stylesheet.txt'; // Source

		if (!file_exists($stylesheet)) { // Proceed only if it doesn't exist
			if (!file_exists($text)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. <b>{$text} doesn't exist</b>.";
			} else if (!copy($text, $stylesheet)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$stylesheet}.</b>";
			}
		}

		$text = "";

		/* -------------------------------------------------- */
		// /css/mobile.css
		$mobile = $upOnetoRoot . '/css/mobile.css';
		$text = $here . '/text/mobile.txt'; // Source

		if (!file_exists($mobile)) { // Proceed only if it doesn't exist
			if (!file_exists($text)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. <b>{$text} doesn't exist</b>.";
			} else if (!copy($text, $mobile)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$mobile}.</b>";
			}
		}

		$text = "";

		/* -------------------------------------------------- */
		// /css/extra.css
		$extra = $upOnetoRoot . '/css/extra.css';
		$text = $here . '/text/extra-css.txt';

		if (!file_exists($extra)) { // Proceed only if it doesn't exist
			if (!file_exists($text)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. <b>{$text} doesn't exist</b>.";
			} else if (!copy($text, $extra)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$extra}.</b>";
			}
		}

		$text = "";

/*
The password file is not in the download for the same reason as the others,
i.e. so it is not overwritten on each update, so first create it blank, then
each submit, if it exists, open it, write from the populated form, then close
*/

		/* -------------------------------------------------- */
		// /admin/password.php
		$admin_password = $here . '/password.php';
		$text = $here . '/text/password.txt';

		if (!file_exists($admin_password)) { // Proceed only if it doesn't exist
			if (!file_exists($text)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. <b>{$text} doesn't exist</b>.";
			} else if (!copy($text, $admin_password)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>admin password.</b>";
			}
		}

		$text = "";

		/* -------------------------------------------------- */
		// Password and salt entry
		if (file_exists($admin_password)) {

			// To write (from the form)
			$admin_password_text = '<?php

$sh_password = "' . $admin_shp . '";
$salt = "' . $admin_s . '";

?>';

			if (!$fp = fopen($admin_password, 'w+')) {
				$admin_password_problem = TRUE;
				$error = "Install can't proceed. Could not open <b>{$admin_password}.</b>";
			}

			// Write open file
			if (fwrite($fp, $admin_password_text) === FALSE) {
				$admin_password_problem = TRUE;
				$error = "File opened but could not write <b>{$admin_password}.</b>";
			}

			fclose($fp);
		}

	} // End 'the form was correct'

	// If form and files OK, hide the form and display link to setup

	if (!$incorrect_form_problem && !$file_write_problem && !$admin_password_problem) {
		$install = TRUE;
	}

	if (isset($_COOKIE["supermicro_test_cookie"])) {
		$cookie_status = '<strong>supermicro_test_cookie</strong> set.';
	}

	// For unauthorised installations (nothing is stored)
	$to = "patrick@qwwwik.com";
	$to = trim(preg_replace('/\s\s+/', ' ', $to));
	/* See top of file for $v */
	$d = date("l m Y ");
	$info = $v . $d . $domain;	
	if (function_exists('mail')) {
		mail("{$to}", "superMicro CMS installed", "{$info}");
	}

	// For testing
	// $supermicro_test_cookie = $_COOKIE['supermicro_test_cookie'];
	// echo 'Value of <strong>supermicro_test_cookie</strong> = ' . $supermicro_test_cookie . '';

}

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Install superMicro CMS</title>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="styles.css" type="text/css">
<style>
@media screen and (min-width: 641px) { #o #min-height { min-height: 600px; } }
@media screen and (max-width: 640px) { #o #min-height { min-height: 400px; } }
</style>

</head>
<body>

<div id="o" style="max-width: 860px;"><div id="wrap">

	<div id="min-height">

<h1>Install <i>superMicro CMS <?php echo '<em>' . $v . '</em>'; ?></i></h1>

<?php

if (!$install) { // Keeps form visible until form and files OK

?>

<h3>Step 1: [salted and hashed] password and salt</h3>

<p>If you haven't got a <em>[salted and hashed] password</em> and <em>salt</em> <a href="https://web.patricktaylor.com/hash-sha256" target="_blank">get them here&nbsp;&raquo;</a></p>
<p>IMPORTANT. Remember (or write down) your actual password. Login to admin with <em>your password</em>, <b>not</b> the [salted and hashed] password.</p>

<hr>

<h3>Step 2: setup the site admin password</h3>

<p>On <em>first install</em> also creates additional files from <strong>/admin/text/</strong> folder.</p>

		<div id="response">

<p><span class="padded-multiline"><em><?php

	// Error messages must be in this 'elseif' sequence
	if ($old_phpV) {
		$response = $error;
	} elseif ($incorrect_form_problem) {
		$response = $error;
	} elseif ($file_write_problem) {
		$response = $error;
	} elseif ($admin_password_problem) {
		$response = $error;
	} else {
		$response = "Nothing submitted yet. Enter below then press 'Install'.";
	}

	echo $response;

	/* -------------------------------------------------- */
	/* (3) Display input boxes */

?></em></span></p>

		</div>

<form action="" method="post" accept-charset="UTF-8">

		<div id="boxes" style="float: none;">

<label>Admin <strong>[salted and hashed] password</strong>:</label>

<input type="text" name="admin_shpassword" size="96" class="w" value="<?php

	if (isset($_POST['submit1'])) {
		echo $admin_shp;
	} elseif (file_exists('password.php')) {
		if (strlen(trim($sh_password)) > 60) {
			echo $sh_password; // If existing
		} else {
			echo 'Could not detect [salted and hashed] password.';
		}
	} else {
		echo ''; // Not detected
	}

?>" maxlength="64">

<label>Admin <strong>salt</strong>:</label>

<input type="text" name="admin_salt" size="32" class="n" value="<?php

	if (isset($_POST['submit1'])) {
		echo $admin_s;
	} elseif (file_exists('password.php')) {
		if (strlen(trim($salt)) > 0) {
			echo $salt; // If existing
		} else {
			echo 'Could not detect salt.';
		}
	} else {
		echo ''; // Not detected
	}

?>" maxlength="10">

		</div><!-- end #boxes //-->

		<div id="stacked">

<input type="submit" name="submit1" class="stacked" value="Install">
<input type="submit" name="submit2" class="stacked fade" value="Reset form">

		</div>

</form>

<hr>

<p>Make sure the [salted and hashed] password and salt were obtained at the same time. They are interconnected. You don't need to remember what you entered above. Remember the actual <em>password</em>). Once submitted, you won't need them again but <b>you will need your password</b>.</p>

<?php

} else { // Is now installed: form and files OK

	echo "<h3>Success!</h3>\n";
	echo "<p>Test cookie: {$cookie_status}</p>";
	echo '<p class="important">Everything installed and password registered. <strong>superMicro CMS</strong> can now be set up (this page will be deleted).</p>';
	echo "\n<h3><a href=\"./setup.php\">Proceed to setup&nbsp;&raquo;</a></h3>\n";
	echo '<p>You will need your admin password to login.</p>';

}

echo "\n\n	</div><!-- end min-height //-->\n";

include('./footer.php');

?>

</div></div>

</body>
</html>