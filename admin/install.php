<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

// Last updated 23 March 2023

define('ACCESS', TRUE);

// Declare variables
$sh_password = $salt = $domain = $cookie_status = $test_cookie = $missing_password_php = $incorrect_form_problem = $admin_shp = $admin_s = $file_write_problem = $error = $admin_password_problem = $install = $siteID = '';

error_reporting(0);
// error_reporting(E_ALL);

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

if (version_compare(phpversion(), '5.2.0', '<')) {
	$error = 'superMicro CMS needs PHP version 5.2.0 or later. Your server is running PHP version ' . PHP_VERSION . '. Try installing anyway.';
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
/* Verify all the required admin files */

$required = array('./backup.php', './comments.php', './footer.php', './functions.php', './htaccess.php', './images.php', './index.php', './language.php', './list.php', './login-form.php', './nav.php', './setup.php', './stopwords.php', './top.php', './upload.php', './text/count.txt', './text/extra-css.txt', './text/index.txt', './text/inmenu.txt', './text/listhits.txt', './text/pageid.txt', './text/password.txt', './text/since.txt', './text/stylesheet.txt', './text/tempcount.txt', './text/tempcountreset.txt');

foreach ($required as $file) {
	if (!file_exists($file)) { // Exit if a file is missing
		echo "Error: the file '{$file}' does not exist. It must be installed.";
		exit();
	}
}

// Get functions.php
require('./functions.php');

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

		// /pages/index.txt
		$index = '../pages/index.txt'; // Destination
		if (!file_exists($index)) { // If it exists, leave it alone
			$text = './text/index.txt'; // Source
			if (!copy($text, $index)) { // If it wasn't written
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$index}.</b>";
			}
		}

		// /inc/inmenu.txt
		$inmenu = '../inc/inmenu.txt';
		if (!file_exists($inmenu)) {
			$text = './text/inmenu.txt';
			if (!copy($text, $inmenu)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$inmenu}.</b>";
			}
		}

		// /css/stylesheet.css
		$stylesheet = '../css/stylesheet.css';
		if (!file_exists($stylesheet)) {
			$text = './text/stylesheet.txt';
			if (!copy($text, $stylesheet)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$stylesheet}.</b>";
			}
		}

		// /css/extra.css
		$extra = '../css/extra.css';
		if (!file_exists($extra)) {
			$text = './text/extra-css.txt';
			if (!copy($text, $extra)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$extra}.</b>";
			}
		}

		// /admin/visits/count.txt
		$count = './visits/count.txt';
		if (!file_exists($count)) {
			$text = './text/count.txt';
			if (!copy($text, $count)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$count}.</b>";
			}
		}

		// /admin/visits/listhits.txt
		$listhits = './visits/listhits.txt';
		if (!file_exists($listhits)) {
			$text = './text/listhits.txt';
			if (!copy($text, $listhits)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$listhits}.</b>";
			}
		}

		// /admin/visits/pageid.txt
		$pageid = './visits/pageid.txt';
		if (!file_exists($pageid)) {
			$text = './text/pageid.txt';
			if (!copy($text, $pageid)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$pageid}.</b>";
			}
		}

		// /admin/visits/since.txt
		$since = './visits/since.txt';
		if (!file_exists($since)) {
			$text = './text/since.txt';
			if (!copy($text, $since)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$since}.</b>";
			}
		}

		// /admin/visits/tempcount.txt
		$tempcount = './visits/tempcount.txt';
		if (!file_exists($tempcount)) {
			$text = './text/tempcount.txt';
			if (!copy($text, $tempcount)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$tempcount}.</b>";
			}
		}

		// /admin/visits/tempcount.txt
		$tempcount_reset = './visits/tempcountreset.txt';
		if (!file_exists($tempcount_reset)) {
			$text = './text/tempcountreset.txt';
			if (!copy($text, $tempcount_reset)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$tempcount_reset}.</b>";
			}
		}

/*
The password file is not in the download for the same reason as the others,
i.e. so it is not overwritten on each update, so first create it blank, then
each submit, if it exists, open it, write from the populated form, then close
*/

		// /admin/password.php
		$admin_password = './password.php';
		if (!file_exists($admin_password)) {
			$text = './text/password.txt';
			if (!copy($text, $admin_password)) {
				$file_write_problem = TRUE;
				$error = "Install can't proceed. Could not create <b>{$admin_password}.</b>";
			}
		}

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
<link rel="stylesheet" href="stylesheet.css" type="text/css">

</head>
<body>

<div id="wrap">

	<div class="min-height">

<h1>Install <i>superMicro CMS</i></h1>

<?php

if (!$install) { // Keeps form visible until form and files OK

?>

<h3>Step 1: [salted and hashed] password and salt</h3>

<p>If you haven't got a <em>[salted and hashed] password</em> and <em>salt</em> <a href="https://patricktaylor.com/hash-sha256" target="_blank">get them here&nbsp;&raquo;</a></p>
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

		<div id="boxes">

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

		</div>

		<div id="buttons" class="install">

<input type="submit" name="submit1" class="big" value="Install">
<input type="submit" name="submit2" class="fade" value="Reset form">

		</div>

</form>

<hr style="clear: both;">

<p>Make sure the [salted and hashed] password and salt were obtained at the same time. They are interconnected. You don't need to remember what you entered above (neither is your actual <em>password</em>). Once submitted, you won't need them again but <b>you will need your password</b>.</p>

<?php

} else { // Is now installed: form and files OK

	echo "<h3>Success!</h3>\n";
	echo "<p>Test cookie: {$cookie_status}</p>";
	echo '<p class="important">Everything installed and password registered. <strong>superMicro CMS</strong> can now be set up (this page will be deleted).</p>';
	echo "\n<h3><a href=\"./setup.php\">Proceed to setup&nbsp;&raquo;</a></h3>\n";
	echo '<p>You will need your admin password to login.</p>';

}

echo '</div><!-- end min-height //-->';

include('./footer.php');

?>

</div>

</body>
</html>