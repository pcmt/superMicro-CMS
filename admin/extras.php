<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 20 May 2024 */

define('ACCESS', TRUE);

// Declare variables
$page = $filetitle = $file_contents = $response = $rewrite = $ext = $update = "";

$thisAdmin = 'extras'; // For nav

require('./top.php');

// For $fileurl link to successful update
if (APACHE) {
	$ext = '';
} else {
	$ext = '.php';
}

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php

if (function_exists('p_title')) {
	p_title('extras');
} else {
	_print('Install the latest version of functions.php');
}

?></title>
<?php if (file_exists('../inc/settings.php')) { ?>
<link rel="shortcut icon" href="<?php _print(LOCATION); ?>favicon.ico">
<?php } ?>
<meta name="robots" content="noindex,nofollow">
<link rel="stylesheet" href="styles.css" type="text/css">

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

	if (isset($_GET['page'])) {
		$page = $_GET['page'];
		if ($page) {
			$filetitle = trim($page);
			$extrasfilename = $filetitle . '.txt';
			if (file_exists("../extras/{$extrasfilename}")) {
				$file_contents = stripslashes(file_get_contents("../extras/{$extrasfilename}"));
				if ($filetitle == 'index') {
					$fileurl = LOCATION;
				} else {
					$fileurl = LOCATION . $filetitle . $ext;
				}
				$response = "<em>Extras on <b><a href=\"{$fileurl}\">{$filetitle}</a></b>. <a href=\"index.php?page={$filetitle}\">Edit page</a>&nbsp;&raquo;</em>";
			} else {
				$response = '<em>Sorry, this extras file does not exist.</em>';
			}
		} else {
			$response = '<em>No page found.</em>';
		}
	}

?>

<div id="o"><div id="wrap">

<h1><?php

	if (function_exists('h1')) {
		h1('extras');
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

/* ---------------------------------------------------------------------- */
/* Get extras */

	if (isset($_POST['get_extras'])) {
		if (strlen($_POST['extras_id']) < 1) {
			$response = "<em>You didn't enter a page name.</em>";
		} else {
			$filetitle = trim($_POST['extras_id']);
			$extrasfilename = $filetitle . '.txt';
			if (!file_exists("../extras/{$extrasfilename}")) {
				$response = '<em>Sorry, this extras file does not exist. Try another.</em>';
			} else {
				$file_contents = stripslashes(file_get_contents("../extras/{$extrasfilename}"));
				if ($filetitle == 'index') {
					$fileurl = LOCATION;
				} else {
					$fileurl = LOCATION . $filetitle . $ext;
				}
				$response = "<em>Extras on <b><a href=\"{$fileurl}\">{$filetitle}</a></b></em>";
			}
		}
	}

/* ---------------------------------------------------------------------- */
/* Prepare to edit extras */

	if (isset($_POST['presubmit'])) {
		if (trim($_POST['extras_id']) == '') {
			$response = "<em>You didn't enter a page name.</em>";
			$update = FALSE;
		} elseif (trim($_POST['extras']) == '') {
			$response = "<em>You didn't enter any text.</em>";
			$update = FALSE;
		} else {
			$update = TRUE;
		}

		if ($update) {
			$filetitle = trim($_POST['extras_id']);
			$extrasfilename = $filetitle . '.txt';
			if (!file_exists("../extras/{$extrasfilename}")) {
				$response = '<em>Sorry, this extras file does not exist. Try another.</em>';
			} else {
				$response = "<em>You are about to edit extras on <b>{$filetitle}</b> &raquo; click 'Update extras' again, or [ <a href=\"index.php?page={$filetitle}\" title=\"Abort\">abort</a> ]</em>";
			}
		}
	}

/* ---------------------------------------------------------------------- */
/* Edit extras */

	if (isset($_POST['submit'])) {
		$filetitle = trim($_POST['extras_id']);
		if ($filetitle == '') {
			$response = "<em>You didn't enter a page name.</em>";
			$update = FALSE;
		} elseif (trim($_POST['extras']) == '') {
			$response = "<em>You didn't enter any text.</em>";
			$update = FALSE;
		} else {
			$update = TRUE;
		}

		if ($update) {
			$extrasfilename = $filetitle . '.txt';
			if (!file_exists("../extras/{$extrasfilename}")) {
				$response = '<em>Sorry, this extras file does not exist. Try another.</em>';
			} else {
				$extrasfilename = "../extras/{$extrasfilename}";
				$extrascontent = stripslashes($_POST['extras']);
				$fp = fopen($extrasfilename, 'w+');
				fwrite($fp, $extrascontent);
				fclose($fp);
				if ($filetitle == 'index') {
					$fileurl = LOCATION;
				} else {
					$fileurl = LOCATION . $filetitle . $ext;
				}
				$response = "<em>Extras on <b><a href=\"{$fileurl}\">{$filetitle}</a></b> were successfully updated. <a href=\"index.php?page={$filetitle}\">Edit page</a>&nbsp;&raquo;</em>";
			}
		}
	}

?>

<h3>Create/edit/delete extras</h3>

	<div id="response">

<?php

	_print('<p><span class="padded-multiline">');
	if (!$response) {
		_print('<em>No action requested.</em>');
	} else {
		_print($response);
	}
	_print('</span></p>');

?>

	</div>

<form action="<?php _print($self); ?>" method="post" accept-charset="UTF-8">

	<div id="boxes">

<label>Page title:</label>
<input type="text" name="extras_id" size="60" value="<?php

	if (isset($_POST['presubmit']) || isset($_POST['get_extras'])) {
		_print($_POST['extras_id']);
	} elseif (isset($_POST['submit'])) {
		if (strlen($filetitle) < 1) {
			$filetitle = 'index';
		}
		_print($filetitle);
	} else {
		_print($page);
	}

?>
" maxlength="60">

<label>Edit extras.</label>

		<div class="textarea-container">

<textarea class="flexitem" name="content" rows="20">
<?php

	if (isset($_POST['presubmit']) || isset($_POST['submit'])) {
		_print(stripslashes(htmlentities($_POST['extras'])));
	} elseif (isset($_POST['get_extras']) || $page) {
		_print(stripslashes(htmlentities($file_contents)));
	}

?>
</textarea>

		</div>

	</div>

	<div id="buttons">

<input type="submit" name="get_extras" class="fade" value="Get extras">
<input type="submit" name="<?php

	if (isset($_POST['presubmit']) && $update) { // Move forward only if not whitespace
		_print('submit');
	} else {
		_print('presubmit');
	}

?>" value="Update extras">
	</div>

</form>

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