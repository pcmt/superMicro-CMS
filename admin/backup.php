<?php
/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 29 August 2024 */

define('ACCESS', TRUE);

// Declare variables
$_pages = $_images = $response = $file_in_folder = $canZIP = "";

$thisAdmin = 'backup'; // For nav

include('./top.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php p_title('backup'); ?></title>
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

?>

<div id="o"><div id="wrap">

<h1><?php h1('backup'); ?></h1>

<?php

includeFileIfExists('./nav.php');

?>

<h3>Create/download/delete backup.zip of content files</h3>

<?php

	// Check this
	if (class_exists('ZipArchive')) {
		$canZIP = TRUE;
	} else {
		$canZIP = FALSE;
	}

	// Get installation folder
	$_root = str_replace('\\', '/', dirname(dirname(__FILE__)));
	// echo '$_root = ' . $_root . '<br>';

	/* -------------------------------------------------- */
	// ZIP pages (original code edited by ChatGPT 24/04/24)
	if (isset($_POST['submit1'])) {

		// Define directory to scan
		$_pages = $_root . '/pages/';

		// Open the directory
		if ($files = opendir($_pages)) {

			// Initialize an empty array to hold valid file paths
			$filesArray = array();

			// Loop through each file in the directory
			while (FALSE !== ($file = readdir($files))) {

				// Ignore '.' and '..' folders
				if (in_array($file, array('.', '..'))) {
					continue;
				}

				// Check if the file is a .txt file
				if (strpos($file, '.txt') !== FALSE) {
					// Construct the corresponding .php file name
					$phpfile = str_replace('.txt', '.php', $file);

					// Check if the .php file and its corresponding .txt file exist and are not directories
					if ( !is_dir($_root . '/' . $phpfile) && !is_dir($_root . '/pages/' . $file) && !is_dir($_root . '/comments/' . $file) && !is_dir($_root . '/extras/' . $file) && !is_dir($_root . '/data/' . $file) ) {
						// Add the .php and .txt file paths to the filesArray
						$filesArray[] = $_root . '/' . $phpfile;
						$filesArray[] = $_root . '/pages/' . $file;
						$filesArray[] = $_root . '/comments/' . $file;
						$filesArray[] = $_root . '/extras/' . $file;
						$filesArray[] = $_root . '/data/' . $file;
					}
				}
			}

			// Add additional files to the filesArray
			$filesArray[] = $_root . '/css/stylesheet.css';
			$filesArray[] = $_root . '/css/mobile.css';
			$filesArray[] = $_root . '/css/extra.css';

			// Should now have the files so close the directory
			closedir($files);

// print '<pre>';
// print_r($filesArray);
// print '</pre>';

			// Initialize an empty array to hold valid files
			$valid_files = array();

			// Check each file in filesArray
			foreach ($filesArray as $file) {
				// If the file exists and is a regular file, add it to valid_files array
				if (file_exists($file) && is_file($file)) {
					$valid_files[] = $file;
				}
			}

			// If there are valid files, create a ZIP archive
			if (count($valid_files)) {
				$zip = new ZipArchive(); // Create a new ZipArchive object
				$zipname = "backup.zip"; // Specify the ZIP file name

				// Open the ZIP file in create mode
				if ($zip->open($zipname, ZipArchive::CREATE) === TRUE) {
					// Add each valid file to the ZIP archive
					foreach ($valid_files as $file) {
						// Add file to ZIP archive with the same name
						$file_in_folder = ( basename(dirname($file)) . '/' . basename($file) );
						$zip->addFile($file, $file_in_folder); // Includes folders
					}
					// Close the ZIP archive
					$zip->close();
				}
			}

			// Generate response message based on whether backup was successful
			if (file_exists('./backup.zip')) {
				$response = '<em>Backup of pages successful. <a href="backup.zip">Download backup</a> then click \'Delete backup\'.</em>';
			} else {
				$response = '<em>Backup did not succeed.</em>';
			}
		}
	}

	/* -------------------------------------------------- */
	// ZIP images
	// No function, no array, different to pages (only one folder)
	if (isset($_POST['submit2'])) {

		if (!file_exists('./backup.zip')) {

			$source = $_root . '/img/'; // Source folder
			$backup_ZIP = './backup.zip'; // New ZIP file

			// Create new zip class
			$zip = new ZipArchive;

			if ($zip->open($backup_ZIP, ZipArchive::CREATE ) === TRUE) {

				// Store the path into the variable
				$dir = opendir($source);
				while($file = readdir($dir)) {
					if(is_file($source . $file)) {
						$zip->addFile($source . $file, $file);
					}
				}

				$zip->close();

				$response = '<em>Image backup successful. <a href="backup.zip">Download backup</a> then click \'Delete backup\'.</em>';

			} else {
				$response = '<em>Image backup failed. Check status&#8212;does a backup already exist?</em>';
			}

		} else {
			$response = '<em>Backup already exists. <a href="backup.zip">Download backup</a> then click \'Delete backup\'.</em>';
		}
	}

	/* -------------------------------------------------- */
	// ZIP uploads
	// No function, no array, different to pages (only one folder)
	if (isset($_POST['submit3'])) {

		if (!file_exists('./backup.zip')) {

			$source = $_root . '/uploads/'; // Source folder
			$backup_ZIP = './backup.zip'; // New ZIP file

			// Create new zip class
			$zip = new ZipArchive;

			if ($zip->open($backup_ZIP, ZipArchive::CREATE ) === TRUE) {

				// Store the path into the variable
				$dir = opendir($source);
				while($file = readdir($dir)) {
					if(is_file($source . $file)) {
						$zip->addFile($source . $file, $file);
					}
				}

				$zip->close();

				$response = '<em>Uploads backup successful. <a href="backup.zip">Download backup</a> then click \'Delete backup\'.</em>';

			} else {
				$response = '<em>Uploads backup failed. Check status&#8212;does a backup already exist?</em>';
			}

		} else {
			$response = '<em>Backup already exists. <a href="backup.zip">Download backup</a> then click \'Delete backup\'.</em>';
		}
	}

/* ---------------------------------------------------------------------- */
/* Delete backup */

	if (isset($_POST['submit4'])) {

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

	if (isset($_POST['submit1']) || isset($_POST['submit2']) || isset($_POST['submit3']) || isset($_POST['submit4'])) {
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

	<div id="backup-buttons">

<input type="submit" name="submit1" value="Backup pages">
<input type="submit" name="submit2" value="Backup images">
<input type="submit" name="submit3" value="Other uploads">
<input type="submit" name="submit4" value="Delete backup">
<input type="submit" name="submit5" value="Check status">

	</div>

</form>

	<div id="info">

<p>This utility backs up content files or images into a ZIP file named <strong>backup.zip</strong> for downloading to your hard drive. This is the user-generated content, not the whole installation of <em>Qwwwik</em>. Select <strong>pages</strong> (written content files) or <strong>images</strong>. To do both, download then delete one before doing the other.</p>
<p>Backup ZIPs may contain:</p>
<ul>
<li>The written content pages you have created.</li>
<li>All associated comments and 'extras' files.</li>
<li>The menu file.</li>
<li>The stylesheets 'stylesheet.css' and 'extra.css'.</li>
<li>The contents of the images folder, including any images you uploaded. There may be a limit imposed by the Operating System or the server administrator (usually memory limit or maximum execution time). This may be an issue if you have a large number of images to zip. Tested to work with up to 250 average size jpg files (about 10 seconds).</li>
</ul>
<p>For security, after creating a backup do not leave it on the server. Download it to your hard drive by clicking 'Download backup' then click 'Delete backup'. Check whether a backup exists on the server by clicking 'Check status'.</p>
<p>See also <a href="https://qwwwik.com/backups" target="_blank">restoring from backups&nbsp;&raquo;</a></p>

	</div>

<?php

	if ($canZIP) {
		_print("<h3>Your server supports ZIP archiving</h3>\n<p class=\"backup\">If your backup is large (images especially) please wait a few moments after pressing the button.</p>");
	} else {
		_print("<h3>Note: your server does not support ZIP archiving</h3>\n<p class=\"backup\">Backup your files by downloading them manually (eg: via FTP).</p>");
	}

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