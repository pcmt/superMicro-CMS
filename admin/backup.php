<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 21 Dec 2020 */

// Declare variables
$_pages = $_images = $response = "";

require('./top.php');

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php

if (function_exists('p_title')) {
	p_title('backup');
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

?>

<div id="wrap">

<h1><?php

if (function_exists('h1')) {
	h1('backup');
} else {
	_print('Install the latest version of functions.php');
}
?></h1>

<p id="nav"><a href="<?php _print(LOCATION); ?>">&#171;&nbsp;Site</a> 
<a href="./index.php" title="Create/edit/delete pages">Pages</a> 
<a href="./images.php" title="Upload or delete images">Images</a> 
<a href="./htaccess.php" title="Create .htaccess file">.htaccess</a> 
<span>Backup</span> 
<a href="./setup.php" title="Setup">Setup</a> 
<a href="./visits/" title="Visits" target="_blank">Visits</a> 
<a href="?status=logout" title="Logout">Logout</a> 
<a href="https://supermicrocms.com/information" title="Help" class="ext" target="_blank">Help&nbsp;&#187;</a></p>

<h3>Create/download/delete backup.zip of content files</h3>

<?php

	// Get installation folder
	$_root = str_replace('\\', '/', dirname(dirname(__FILE__)));
	// echo '$_root = ' . $_root . '<br>';

	/* -------------------------------------------------- */
	// ZIP pages
	if (isset($_POST['submit1'])) {

		$destination = './backup.zip';
		$type = 'of pages ';
		$_pages = $_root . '/pages';

		if ($files = opendir($_pages)) {

			$filesArray = array(); // New array
			while (FALSE !== ($file = readdir($files))) {

				// Ignore '.' and '..' folders
				if (in_array(substr($file, strrpos($file, '/')+1), array('.', '..'))) {
					continue;
				}

				// If .txt file exists in /pages/
				if (strpos($file, '.txt') !== FALSE) {
					$txtfile = str_replace($_root . '/', '', $file); // Get the filename
					$phpfile = str_replace('.txt', '.php', $txtfile); // Make a .php file

					if (!is_dir('../' . $txtfile)) {
						// Add files into the array
						$filesArray[] = $_root . '/' . $phpfile;
						$filesArray[] = $_root . '/pages/' . $txtfile;
					}
				}
			}

			// Additional files
			$filesArray[] = $_root . '/inc/inmenu.txt';
			$filesArray[] = $_root . '/css/extra.css';
			$filesArray[] = $_root . '/css/stylesheet.css';

			closedir($files);

			// print '<pre>';
			// print_r($filesArray);
			// print '</pre>';

		} else { // No files
			$_pages = FALSE;
			$response = '<em>Error. Could not open the pages folder.</em>';
		}
	}

	/* -------------------------------------------------- */
	// ZIP images
	if (isset($_POST['submit2'])) {
		$destination = './backup.zip';
		$type = 'of images ';
		$_images = $_root . '/img';

		if ($files = opendir($_images)) {

			$filesArray = array(); // New array
			while (FALSE !== ($file = readdir($files))) {

				// Ignore '.' and '..' folders
				if (in_array(substr($file, strrpos($file, '/')+1), array('.', '..'))) {
					continue;
				}

				$imgfile = str_replace($_root . '/', '', $file);  // Get the filename
				if (!is_dir('../' . $imgfile)) {

					// Ignore .php files
					if (strpos($file, '.php') !== FALSE) {
						continue;
					}

					// Add file into the array
					$filesArray[] = $_root . '/img/' . $imgfile;
				}
			}

			closedir($files);

			// print '<pre>';
			// print_r($filesArray);
			// print '</pre>';

		} else { // No files
			$_images = FALSE;
			$response = '<em>Error. Could not open the images folder.</em>';
		}
	}

/* ---------------------------------------------------------------------- */
/* Create backup */

	if ($_pages || $_images) {

		$backup = zip($filesArray, $destination); // See functions.php

		if ($backup) {
			if (file_exists('./backup.zip')) {
				$response = '<em>Backup ' . $type . 'successful. <a href="backup.zip">Download backup</a> then click \'Delete backup\'.</em>';
			} else {
				$response = '<em>Backup did not succeed.</em>';
			}
		} else {
			$response = '<em>ZIP function failed. Check status&#8212;does a backup already exist?</em>';
		}
	}

/* ---------------------------------------------------------------------- */
/* Delete backup */

	if (isset($_POST['submit3'])) {

		if (file_exists('./backup.zip')) {
			@unlink('./backup.zip');
			if (!file_exists('./backup.zip')) {
				$response = '<em>Backup deleted.</em>';
			} else {
				$response = '<em>The backup could not be deleted.</em>';
			}
		} else {
			$response = '<em>A backup does not exist.</em>';
		}
	}

?>

	<div id="response">

<?php

	_print('<p><span class="padded-multiline">');

	if (isset($_POST['submit1']) || isset($_POST['submit2']) || isset($_POST['submit3'])) {
		_print($response);
	} elseif (file_exists('./backup.zip')) {
		_print('<em>A backup exists on the server and should be deleted. <a href="backup.zip">Download it</a> then click \'Delete backup\'.</em>');
	} else {
		_print('<em>A backup does not exist.</em>');
	}

	_print('</span></p>');

?>

	</div>

<form action="<?php _print($self); ?>" method="post" accept-charset="UTF-8">

	<div id="boxes3">

<input type="submit" name="submit1" value="Backup pages">
<input type="submit" name="submit2" value="Backup images">
<input type="submit" name="submit3" value="Delete backup">
<input type="submit" name="submit4" value="Check status">


	</div>

</form>

	<div id="info">

<p>This utility backs up content files or images into a ZIP file named <strong>backup.zip</strong> for downloading to your hard drive. This is the user-generated content, not the whole installation of <em>la.plume Micro CMS</em>. Select <strong>pages</strong> (written content files) or <strong>images</strong>. To do both, download then delete one before doing the other.</p>
<p>Backup ZIPs may contain:</p>
<ul>
<li>The written content pages you have created.</li>
<li>The menu file.</li>
<li>The stylesheets 'stylesheet.css' and 'extra.css' (backed up with pages).</li>
<li>The contents of the images folder, including any images you uploaded. Please note: there may be a limit imposed by the Operating System or server administrator on the total size of the ZIP file. This may be an issue if you have a large number of images.</li>
<li>Server error logs (if they exist).</li>
</ul>
<p>For security, after creating a backup do not leave it on the server. Download it to your hard drive by clicking 'Download backup' then click 'Delete backup'. Check whether a backup exists on the server by clicking 'Check status'.</p>
<p>See also <a href="https://supermicrocms.com/backups" target="_blank">restoring from backups&nbsp;&raquo;</a></p>

	</div>

<?php

	if (class_exists('ZipArchive')) {
		_print("<h3>Your server supports ZIP archiving</h3>\n<p class=\"backup\">If your backup is large (images especially) please wait a few moments after pressing the button.</p>");
	} else {
		_print("<h3>Note: your server does not support ZIP archiving</h3>\n<p class=\"backup\">Backup your files by downloading them manually (eg: via FTP).</p>");
	}

	include('./footer.php');
} else {

	/* -------------------------------------------------- */
	// No $login or !$login

	_print('<p>Login could not be verified.</p>');
}

?>

</div>

</body>
</html>